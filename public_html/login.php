<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/app_config.php';

session_start();

// Check if the user is already logged in and redirect
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $redirectUrl = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '/admin/choose_action';
    header("Location: $redirectUrl");
    exit;
}

$expectedUsername = cfg('admin_username');
$expectedPassword = cfg('admin_password');
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $errorMessage = "Both username and password are required.";
    } elseif (hash_equals((string) $expectedUsername, (string) $username)
        && hash_equals((string) $expectedPassword, (string) $password)) {
        $_SESSION['loggedin'] = true;
        $redirectUrl = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '/admin/choose_action';
        header("Location: $redirectUrl");
        exit;
    } else {
        $errorMessage = "Invalid username or password.";
    }
}
?>

<?php include 'Header.php'; ?>

<section class="bg-img2 error-404">
    <header class="page-header">
        <h2 class="title">LOGIN</h2>
    </header>
    <div class="container">
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger" role="alert"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form action="login" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input name="username" class="form-control" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input name="password" class="form-control" id="password" required type="password">
            </div>
            <input name="option" type="hidden" value="<?php echo isset($_POST['option']) ? $_POST['option'] : ''; ?>">
            <button class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill" type="submit">Login</button>
        </form>

        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
            <div class="alert alert-success" role="alert">You're logged in successfully!</div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
