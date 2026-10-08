<?php include 'Header.php'; ?>

<?php
require_once __DIR__ . '/form_guard.php';

form_guard_session();

$name = $occupation = $mobile = $email = $city = $diet = "";
$errors = array();
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name       = form_guard_text($_POST['name'] ?? '', 80);
    $occupation = form_guard_text($_POST['occupation'] ?? '', 80);
    $mobile     = form_guard_text($_POST['mobile'] ?? '', 20);
    $email      = form_guard_text($_POST['email'] ?? '', 120);
    $city       = form_guard_text($_POST['city'] ?? '', 80);
    $diet       = form_guard_text($_POST['diet'] ?? '', 300);

    $mobileDigits = form_guard_mobile($mobile);

    // Spam layers run first and fail silently, so a bot is never told why.
    $spamReason = '';
    if (!form_guard_honeypot_ok()) {
        $spamReason = 'honeypot';
    } elseif (!form_guard_time_ok()) {
        $spamReason = 'time-trap';
    } elseif (!form_guard_csrf_ok()) {
        $spamReason = 'csrf';
    } elseif (form_guard_rate_limited()) {
        $spamReason = 'rate-limit';
    } else {
        $spamReason = form_guard_content_is_spam(
            array($name, $occupation, $email, $city, $diet),
            $diet,
            array($name, $occupation, $city)
        );
    }

    if ($spamReason !== '') {
        form_guard_log($spamReason, array('email' => substr($email, 0, 40)));
        form_guard_flash_set('success', 'Data submitted successfully.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Every field is mandatory, checked on the server where it counts.
    if ($name === '' || form_guard_len($name) < 2) {
        $errors[] = 'Please enter your full name.';
    }
    if ($occupation === '' || form_guard_len($occupation) < 2) {
        $errors[] = 'Please enter your occupation.';
    }
    if ($mobile === '') {
        $errors[] = 'Please enter your mobile number.';
    } elseif (!form_guard_is_mobile($mobileDigits)) {
        $errors[] = 'Please enter a valid 10-digit mobile number.';
    }
    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($city === '' || form_guard_len($city) < 2) {
        $errors[] = 'Please enter your city.';
    }
    if ($diet === '' || form_guard_len($diet) < 5) {
        $errors[] = 'Please tell us your diet requirement.';
    }

    if (empty($errors) && form_guard_recent_duplicate($mobileDigits)) {
        form_guard_log('duplicate', array('mobile' => substr($mobileDigits, 0, 4) . '******'));
        form_guard_flash_set('success', 'Data submitted successfully.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    if (empty($errors)) {
        $users = jd_read('user_details');
        $users[] = array(
            'id'         => jd_next_id($users),
            'name'       => $name,
            'occupation' => $occupation,
            'mobile'     => $mobileDigits,
            'email'      => $email,
            'city'       => $city,
            'diet'       => $diet,
            'created_at' => date('Y-m-d H:i:s')
        );
        if (jd_write('user_details', $users)) {
            form_guard_rate_record();
            form_guard_flash_set('success', 'Data submitted successfully.');
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
        $errors[] = 'Failed to submit data. Please try again.';
    }
} else {
    $flash = form_guard_flash_take();
    if ($flash && $flash['type'] === 'success') {
        $successMessage = $flash['message'];
    }
}

$esc = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>

<div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-md-12"><div class="ttm-textcolor-white title-box"><div class="ttm-page-title-heading"><h1 class="title">Registration</h1></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Registration</span></span></div></div></div></div></div></div>

<section class="break-991-colum checkout-section clearfix ttm-row"><div class="container"><div class="row"><div class="col-lg-12"><div class="container">
<?php if (!empty($successMessage)): ?><div class="alert mt-3 alert-success"><?php echo $esc($successMessage); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="alert mt-3 alert-danger"><strong>Please correct the following:</strong><ul class="mb-0 mt-2 pl-3"><?php foreach ($errors as $formError): ?><li><?php echo $esc($formError); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<h2 class="text-center title">Registration Form</h2>
<p class="text-center">All fields are required.</p>
<form action="form" method="POST">
    <?php echo form_guard_hidden_fields(); ?>
    <div class="form-group"><label for="name">Name:</label> <input class="form-control" id="name" name="name" required minlength="2" maxlength="80" autocomplete="name" value="<?php echo $esc($name); ?>"></div>
    <div class="form-group"><label for="occupation">Occupation:</label> <input class="form-control" id="occupation" name="occupation" required minlength="2" maxlength="80" autocomplete="organization-title" value="<?php echo $esc($occupation); ?>"></div>
    <div class="form-group"><label for="mobile">Mobile Number:</label> <input class="form-control" id="mobile" name="mobile" required maxlength="13" inputmode="tel" autocomplete="tel" value="<?php echo $esc($mobile); ?>"></div>
    <div class="form-group"><label for="email">Email:</label> <input class="form-control" id="email" name="email" required maxlength="120" type="email" autocomplete="email" value="<?php echo $esc($email); ?>"></div>
    <div class="form-group"><label for="city">City:</label> <input class="form-control" id="city" name="city" required minlength="2" maxlength="80" autocomplete="address-level2" value="<?php echo $esc($city); ?>"></div>
    <div class="form-group"><label for="diet">Diet Requirement:</label> <textarea class="form-control" id="diet" maxlength="300" minlength="5" name="diet" required rows="2"><?php echo $esc($diet); ?></textarea></div>
    <div class="text-center"><button class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill" type="submit">Submit</button></div>
</form>
</div></div></div></div></section>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How can I contact Bite And Diet?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "You can contact us by filling out the form on this page, calling +91-8826549878, or messaging us on WhatsApp. We typically respond within a few hours."
      }
    },
    {
      "@type": "Question",
      "name": "What happens after I submit the contact form?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Our team reviews your details and gets in touch via phone or WhatsApp to understand your goals and explain the next steps."
      }
    },
    {
      "@type": "Question",
      "name": "Is my personal information safe?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes, all information submitted through the form is kept confidential and used solely to provide personalized consultation."
      }
    },
    {
      "@type": "Question",
      "name": "Can I book a consultation through this form?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Absolutely! The form is designed to collect your details so we can schedule your diet consultation accordingly."
      }
    },
    {
      "@type": "Question",
      "name": "Do I need to pay while filling the form?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No payment is required at the form stage. Once your consultation is confirmed, payment details will be shared by our team."
      }
    },
    {
      "@type": "Question",
      "name": "What should I include in the form?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Please include your full name, contact number, email, location, and any specific goals or health concerns you'd like to address."
      }
    },
    {
      "@type": "Question",
      "name": "How soon will I receive a response?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "You will usually hear from us within 1 to 12 hours depending on the time of day. We strive to respond as promptly as possible."
      }
    }
  ]
}
</script>
<?php include 'footer.php'; ?>