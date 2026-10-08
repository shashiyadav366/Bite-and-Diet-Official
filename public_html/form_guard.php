<?php
// ------------------------------------------------------------------
// form_guard.php - validation + spam protection for public forms.
//
// Layers, cheapest first:
//   1. honeypot field       bots fill every input they find
//   2. signed time trap     real users take more than a few seconds
//   3. CSRF token           the post must come from our own page
//   4. per-IP rate limit    caps how fast one client can submit
//   5. content heuristics   links / link-farm keywords in free text
//   6. duplicate detection  same mobile resubmitted within the hour
//
// Spam layers fail silently: the caller reports ordinary success so a
// bot learns nothing. Only real validation problems are shown to users.
//
// The state files use a .php extension so that even if the directory
// ends up inside the web root they are executed, never served as text.
// ------------------------------------------------------------------

if (defined('FORM_GUARD_LOADED')) {
    return;
}
define('FORM_GUARD_LOADED', true);

// Generous on purpose. If the site sits behind a proxy such as Cloudflare
// every visitor shares one REMOTE_ADDR, so this must not be tight enough
// to reject real enquiries. The honeypot, time trap and CSRF checks do the
// heavy lifting against bots; this is only a flood cap.
define('FORM_GUARD_MAX_PER_HOUR', 30);
define('FORM_GUARD_MIN_FILL_SECONDS', 4);
define('FORM_GUARD_DUPLICATE_WINDOW', 3600);

function form_guard_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function form_guard_secret()
{
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }

    // Preferred source: the central config outside the web root.
    $configPath = __DIR__ . '/app_config.php';
    if (is_file($configPath)) {
        require_once $configPath;
        $value = trim((string) cfg('form_secret', ''));
        if ($value !== '') {
            return $secret = $value;
        }
    }

    // Fallback: legacy per-secret file, if one already exists.
    $file = dirname(__DIR__) . '/form_secret.php';
    if (is_file($file)) {
        $value = trim((string) @include $file);
        if ($value !== '') {
            return $secret = $value;
        }
    }

    $secret = bin2hex(random_bytes(32));
    $php = "<?php\n// Auto-generated. Keep outside the web root.\nreturn '" . $secret . "';\n";
    if (@file_put_contents($file, $php, LOCK_EX) !== false) {
        @chmod($file, 0600);
    }

    return $secret;
}

/**
 * Prefer a directory outside the web root, fall back to data/.
 */
function form_guard_storage_dir()
{
    $outside = dirname(__DIR__);
    if (is_dir($outside) && is_writable($outside)) {
        return $outside;
    }
    $inside = __DIR__ . '/data';
    if (is_dir($inside) && is_writable($inside)) {
        return $inside;
    }
    return sys_get_temp_dir();
}

function form_guard_store_path($name)
{
    return form_guard_storage_dir() . '/' . $name;
}

function form_guard_client_key()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    if ($ip === '') {
        $ip = 'unknown';
    }
    return substr(hash_hmac('sha256', $ip, form_guard_secret()), 0, 32);
}

// ---------------------------------------------------------------- CSRF

function form_guard_csrf_token()
{
    form_guard_session();
    if (empty($_SESSION['form_csrf'])) {
        $_SESSION['form_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['form_csrf'];
}

function form_guard_csrf_ok()
{
    form_guard_session();
    $given   = isset($_POST['form_csrf']) ? (string) $_POST['form_csrf'] : '';
    $session = isset($_SESSION['form_csrf']) ? (string) $_SESSION['form_csrf'] : '';
    if ($given === '' || $session === '') {
        return false;
    }
    return hash_equals($session, $given);
}

// ------------------------------------------------------- time trap

function form_guard_time_ok()
{
    $ts  = isset($_POST['form_ts']) ? (int) $_POST['form_ts'] : 0;
    $sig = isset($_POST['form_sig']) ? (string) $_POST['form_sig'] : '';
    if ($ts <= 0 || $sig === '') {
        return false;
    }

    $expected = hash_hmac('sha256', (string) $ts, form_guard_secret());
    if (!hash_equals($expected, $sig)) {
        return false;
    }

    // Reject forged future timestamps, and sub-second machine posts.
    return (time() - $ts) >= FORM_GUARD_MIN_FILL_SECONDS;
}

// -------------------------------------------------------- honeypot

function form_guard_honeypot_ok()
{
    foreach (array('website', 'url', 'homepage', 'company', 'fax') as $field) {
        if (!empty($_POST[$field])) {
            return false;
        }
    }
    return true;
}

// ------------------------------------------------------ rate limit

function form_guard_rate_read()
{
    $path = form_guard_store_path('form_guard_rate.php');
    if (!is_file($path)) {
        return [];
    }
    $decoded = json_decode((string) @file_get_contents($path), true);
    return is_array($decoded) ? $decoded : [];
}

function form_guard_rate_limited()
{
    $store  = form_guard_rate_read();
    $key    = form_guard_client_key();
    $window = time() - 3600;

    $hits = isset($store[$key]) && is_array($store[$key]) ? $store[$key] : [];
    $hits = array_values(array_filter($hits, function ($t) use ($window) {
        return (int) $t > $window;
    }));

    return count($hits) >= FORM_GUARD_MAX_PER_HOUR;
}

function form_guard_rate_record()
{
    $path = form_guard_store_path('form_guard_rate.php');
    $lock = @fopen($path . '.lock', 'c');
    if (!$lock) {
        return;
    }
    flock($lock, LOCK_EX);

    $store = [];
    if (is_file($path)) {
        $decoded = json_decode((string) @file_get_contents($path), true);
        $store = is_array($decoded) ? $decoded : [];
    }

    $key = form_guard_client_key();
    $window = time() - 3600;
    foreach ($store as $k => $v) {
        $fresh = is_array($v) ? array_filter($v, function ($t) use ($window) {
            return (int) $t > $window;
        }) : [];
        if (empty($fresh)) {
            unset($store[$k]);
        } else {
            $store[$k] = array_values($fresh);
        }
    }
    if (count($store) > 5000) {
        $store = array_slice($store, -5000, null, true);
    }

    $store[$key] = array_merge(
        isset($store[$key]) ? $store[$key] : [],
        array(time())
    );
    @file_put_contents($path, json_encode($store), LOCK_EX);

    flock($lock, LOCK_UN);
    fclose($lock);
}

// -------------------------------------------------------------- log

function form_guard_log($reason, array $context = array())
{
    $path = form_guard_store_path('form_guard_blocked.php');
    $entry = array(
        'at'      => date('c'),
        'reason'  => $reason,
        'ip_hash' => substr(form_guard_client_key(), 0, 12),
    );
    if (!empty($context)) {
        $entry['context'] = $context;
    }

    $lock = @fopen($path . '.lock', 'c');
    if ($lock) {
        flock($lock, LOCK_EX);
    }

    $log = [];
    if (is_file($path)) {
        $decoded = json_decode((string) @file_get_contents($path), true);
        $log = is_array($decoded) ? $decoded : [];
    }
    $log[] = $entry;
    if (count($log) > 300) {
        $log = array_slice($log, -300);
    }
    @file_put_contents($path, json_encode($log, JSON_PRETTY_PRINT), LOCK_EX);

    if ($lock) {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

// ------------------------------------------------------ field rules

function form_guard_text($value, $maxLength)
{
    $value = (string) $value;
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    $value = strip_tags($value);
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = strip_tags($value);
    $value = preg_replace('/\s+/u', ' ', $value);
    $value = trim($value);

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }
    return substr($value, 0, $maxLength);
}

function form_guard_len($value)
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

/**
 * Normalise an Indian mobile number to its bare 10-digit form.
 * Accepts +91 / 91 / 0 prefixes so real users are not punished.
 */
function form_guard_mobile($raw)
{
    $digits = preg_replace('/\D+/', '', (string) $raw);
    if (strlen($digits) === 12 && strpos($digits, '91') === 0) {
        $digits = substr($digits, 2);
    }
    if (strlen($digits) === 11 && $digits[0] === '0') {
        $digits = substr($digits, 1);
    }
    return $digits;
}

function form_guard_is_mobile($digits)
{
    return (bool) preg_match('/^[6-9][0-9]{9}$/', $digits);
}

// -------------------------------------------------- spam heuristics

function form_guard_has_link($text)
{
    return (bool) preg_match(
        '~https?://|www\.|\b[a-z0-9-]+\.(com|net|org|info|biz|xyz|top|icu|ru|cn|io|co|site|online|shop|club|live)\b~i',
        $text
    );
}

function form_guard_spam_keywords($text)
{
    $words = array(
        'seo', 'backlink', 'guest post', 'web design', 'website design',
        'digital marketing', 'first page of google', 'increase traffic',
        'buy followers', 'crypto', 'bitcoin', 'ethereum', 'forex',
        'casino', 'viagra', 'weight loss pill', 'garcinia', 'keto supplement',
        'click here', 'work from home', 'make money fast', 'buy followers now',
        'telegram channel', 'whatsapp group', 'free consultation no',
    );

    $found = array();
    foreach ($words as $word) {
        if (stripos($text, $word) !== false) {
            $found[] = $word;
        }
    }
    return $found;
}

/**
 * A bare 10-digit Indian mobile sitting inside a field that is not the
 * phone field. Generic form-filling bots misassign values this way, which
 * is how "phone" and "work" end up swapped.
 */
function form_guard_has_phone_number($text)
{
    return (bool) preg_match('/(?<!\d)[6-9]\d{9}(?!\d)/', (string) $text);
}

/**
 * Obvious machine filler: repeated characters, keyboard walks, lorem
 * ipsum. Deliberately conservative - ordinary occupation answers such as
 * "Job", "Service" or "homemaker" are real and must never be flagged.
 */
function form_guard_is_gibberish($text)
{
    $t = strtolower(trim((string) $text));
    if ($t === '' || strlen($t) < 3) {
        return false;
    }

    if (preg_match('/^(.)\1{2,}$/u', $t)) {
        return true;
    }
    if (preg_match('/^(\w{2,4})\1+$/u', $t)) {
        return true;
    }

    // Keyboard walks and filler text only. Never add real names or job
    // words here - "Harsh", "Job", "Working" are genuine answers.
    $junk = array(
        'asdf', 'qwer', 'zxcv', 'hjkl', 'qwerty', 'qwertyuiop',
        'lorem', 'ipsum', 'kjhgfdsa', 'sdfsdf', 'asdasd', 'abcd1234',
    );
    foreach ($junk as $word) {
        if (strpos($t, $word) !== false) {
            return true;
        }
    }
    return false;
}

function form_guard_has_nonlatin($text)
{
    return (bool) preg_match(
        '/[\x{0400}-\x{04FF}\x{0600}-\x{06FF}\x{4E00}-\x{9FFF}\x{3040}-\x{30FF}\x{AC00}-\x{D7AF}]/u',
        $text
    );
}

/**
 * True when the submission looks automated rather than a real enquiry.
 *
 * Link and script detection only inspects the free-text field: an email
 * address like someone@example.com would otherwise match the bare-domain
 * pattern and reject every genuine enquiry.
 */
function form_guard_content_is_spam($fields, $freeText = '', $shortText = array())
{
    if ($freeText === '') {
        $freeText = ' ';
    }

    if (form_guard_has_link($freeText)) {
        return 'link';
    }
    if (form_guard_has_nonlatin($freeText)) {
        return 'nonlatin';
    }

    // Cross-field checks on the short fields. Catches generic bots that
    // drop a phone number into name/occupation/city or leave filler junk.
    foreach ($shortText as $value) {
        if (form_guard_has_phone_number($value)) {
            return 'misplaced-phone';
        }
        if (form_guard_is_gibberish($value)) {
            return 'gibberish';
        }
    }

    $blob = implode(' ', array_map('strval', $fields));
    $words = form_guard_spam_keywords($blob);
    if (!empty($words)) {
        return 'keyword:' . implode(',', $words);
    }
    return '';
}

// --------------------------------------------------------- duplicates

function form_guard_recent_duplicate($mobile)
{
    if (!is_file(__DIR__ . '/json_db.php')) {
        return false;
    }
    require_once __DIR__ . '/json_db.php';

    $cutoff = time() - FORM_GUARD_DUPLICATE_WINDOW;
    foreach (jd_read('user_details') as $row) {
        $existing = preg_replace('/\D+/', '', (string) ($row['mobile'] ?? ''));
        if ($existing === $mobile) {
            $created = strtotime((string) ($row['created_at'] ?? ''));
            if ($created && $created > $cutoff) {
                return true;
            }
        }
    }
    return false;
}

// --------------------------------------------------------- flash msg

function form_guard_flash_set($type, $message)
{
    form_guard_session();
    $_SESSION['form_flash'] = array('type' => $type, 'message' => $message);
}

function form_guard_flash_take()
{
    form_guard_session();
    if (empty($_SESSION['form_flash'])) {
        return null;
    }
    $flash = $_SESSION['form_flash'];
    unset($_SESSION['form_flash']);
    return $flash;
}

// --------------------------------------------------------- hidden UI

/**
 * Honeypot + signed timestamp + CSRF token. Call inside the <form>.
 */
function form_guard_hidden_fields()
{
    $ts      = time();
    $sig     = hash_hmac('sha256', (string) $ts, form_guard_secret());
    $csrf    = form_guard_csrf_token();

    $html  = '<input type="hidden" name="form_ts" value="' . $ts . '">' . PHP_EOL;
    $html .= '    <input type="hidden" name="form_sig" value="' . $sig . '">' . PHP_EOL;
    $html .= '    <input type="hidden" name="form_csrf" value="' . htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') . '">' . PHP_EOL;
    $html .= '    <div aria-hidden="true" style="position:absolute!important;left:-9999px!important;top:auto!important;width:1px!important;height:1px!important;overflow:hidden!important;">' . PHP_EOL;
    $html .= '      <label for="website">Website</label>' . PHP_EOL;
    $html .= '      <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" value="">' . PHP_EOL;
    $html .= '    </div>';

    return $html;
}