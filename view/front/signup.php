<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

require_once __DIR__ . '/../../controller/UserController.php';

$controller = new UserController();
$error = '';

$recaptchaSiteKey = '6LeZItYsAAAAAAD7fAjscW4CMSOc1WRy8a1OkJZF';
$recaptchaSecretKey = '6LeZItYsAAAAADrl2okEpBdjWubnmlZarUurdKMS';

function isValidDateFormat($date)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $parts = explode('-', $date);
    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}

function isStrongPassword($password)
{
    if (strlen($password) < 8) {
        return false;
    }

    if (preg_match_all('/[a-zA-Z]/', $password) < 4) {
        return false;
    }

    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return false;
    }

    return true;
}

function isValidAllowedEmail($email)
{
    return preg_match('/^[A-Za-z0-9._%+-]+@(gmail\.com|esprit\.tn)$/', $email);
}

$roles = ['client', 'coach', 'nutritionist'];
$selectedRole = $_POST['role'] ?? ($_GET['role'] ?? 'client');

if (!in_array($selectedRole, $roles, true)) {
    $selectedRole = 'client';
}
function sendConfirmationEmail($email, $prenom, $token)
{
    $mail = new PHPMailer(true);

    try {

        $verifyLink = "http://localhost:8080/smart-meal-planner/view/front/verify_email.php?token=" . urlencode($token);

        $mail->isSMTP();
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'smartmealplanner22@gmail.com';
        $mail->Password = 'zxbr gssd nroz uqtl';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('smartmealplanner22@gmail.com', 'Smart Meal Planner');

        $mail->addAddress($email, $prenom);

        $mail->isHTML(true);

        $mail->Subject = 'Confirm your email';

        $mail->Body = "
            <h2>Hello $prenom</h2>

            <p>Thank you for joining Smart Meal Planner.</p>

            <p>Please click below to confirm your email:</p>

            <a href='$verifyLink'
            style='
                background:#ce1212;
                color:white;
                padding:12px 20px;
                text-decoration:none;
                border-radius:8px;
                display:inline-block;
            '>
                Confirm Email
            </a>
        ";

        $mail->send();

        return true;
    } catch (MailException $e) {
        die("Mailer Error: " . $mail->ErrorInfo);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');
    $sexe = trim($_POST['sexe'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $allowedDomains = ['gmail.com', 'esprit.tn'];

    $emailDomain = strtolower(substr(strrchr($email, "@"), 1));

    if (!in_array($emailDomain, $allowedDomains)) {
        $error = "Please use a Gmail or Esprit email address.";
    }
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
    $role = trim($_POST['role'] ?? 'client');

    $experience = trim($_POST['experience'] ?? '');
    $speciality = trim($_POST['speciality'] ?? '');
    $motivation = trim($_POST['motivation'] ?? '');
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (!in_array($role, $roles, true)) {
        $role = 'client';
    }

    if ($captchaResponse === '') {
        $error = 'Please confirm that you are not a robot.';
    } else {
        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecretKey);
        $captchaResult = $recaptcha->verify($captchaResponse, $_SERVER['REMOTE_ADDR']);

        if (!$captchaResult->isSuccess()) {
            $error = 'Captcha verification failed. Please try again.';
        }
    }

    if ($error === '') {
        if (
            $prenom === '' ||
            $nom === '' ||
            $date_naissance === '' ||
            $sexe === '' ||
            $email === '' ||
            $mot_de_passe === ''
        ) {
            $error = 'Please fill in all fields.';
        } elseif (!isValidDateFormat($date_naissance)) {
            $error = 'Date of birth must be valid.';
        } elseif (!in_array($sexe, ['Female', 'Male'], true)) {
            $error = 'Please select a valid gender.';
        } elseif (!isValidAllowedEmail($email)) {
            $error = 'Email must end with @gmail.com or @esprit.tn.';
        } elseif (!isStrongPassword($mot_de_passe)) {
            $error = 'Password must be at least 8 characters long and include at least 4 letters, 1 number, and 1 special character.';
        } elseif (($role === 'coach' || $role === 'nutritionist') && ($experience === '' || $speciality === '' || $motivation === '')) {
            $error = 'Please complete the required form.';
        } else {
            $emailToken = bin2hex(random_bytes(32));
            $cleanData = [
                'nom' => $nom,
                'prenom' => $prenom,
                'date_naissance' => $date_naissance,
                'email' => $email,
                'mot_de_passe' => $mot_de_passe,
                'role' => $role,
                'statut' => ($role === 'coach' || $role === 'nutritionist') ? 'pending' : 'active',
                'sexe' => $sexe,
                'experience' => ($role === 'client') ? null : $experience,
                'speciality' => ($role === 'client') ? null : $speciality,
                'motivation' => ($role === 'client') ? null : $motivation,
                'email_verified' => 0,
                'email_token' => $emailToken
            ];

            try {
                $controller->store($cleanData);

                $emailSent = sendConfirmationEmail($email, $prenom, $emailToken);

                if ($emailSent) {
                    header('Location: signup.php?email_confirmation=sent');
                    exit;
                } else {
                    $error = "Account created but confirmation email could not be sent.";
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }

    $selectedRole = $role;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Sign Up - Smart Meal Planner</title>

    <link href="../assets/img/favicon.png" rel="icon">
    <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Inter:wght@300;400;500;600;700;800&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">

    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="../assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="../assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #faf7f7, #ffffff, #f8fbfb);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .signup-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 34px 0;
        }

        .signup-shell {
            position: relative;
        }

        .signup-wrapper {
            background: #fff;
            border-radius: 24px;
            overflow: visible;
            box-shadow: 0 18px 55px rgba(0, 0, 0, 0.08);
        }

        .signup-form {
            min-height: 560px;
        }

        .signup-image {
            background:
                linear-gradient(rgba(40, 26, 20, 0.16), rgba(40, 26, 20, 0.34));
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            transition: opacity 0.45s ease, transform 0.45s ease, filter 0.45s ease;
            border-radius: 24px 0 0 24px;
            overflow: hidden;
        }

        .signup-image.animating-out-left {
            opacity: 0.82;
            transform: translateX(-10px) scale(1.02);
            filter: brightness(0.96);
        }

        .signup-image.animating-out-right {
            opacity: 0.82;
            transform: translateX(10px) scale(1.02);
            filter: brightness(0.96);
        }

        .signup-image.animating-in-left {
            opacity: 0.88;
            transform: translateX(10px) scale(1.01);
            filter: brightness(0.98);
        }

        .signup-image.animating-in-right {
            opacity: 0.88;
            transform: translateX(-10px) scale(1.01);
            filter: brightness(0.98);
        }

        .image-overlay {
            position: absolute;
            left: 30px;
            bottom: 30px;
            color: #fff;
            max-width: 320px;
        }

        .image-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 30px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.20);
            backdrop-filter: blur(4px);
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .image-overlay h2 {
            font-family: 'Amatic SC', cursive;
            font-size: 2.7rem;
            line-height: 1;
            margin-bottom: 8px;
            color: #fff;
        }

        .image-overlay p {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.92);
        }

        .signup-form {
            padding: 30px 34px;
            display: flex;
            align-items: center;
            position: relative;
        }

        .form-inner {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .back-home {
            color: #ce1212;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 14px;
        }

        .back-home:hover {
            text-decoration: underline;
        }

        .mini-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            gap: 12px;
        }

        .badge-mini {
            background: rgba(206, 18, 18, 0.08);
            color: #ce1212;
            padding: 6px 13px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .dots {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #e6d2d2;
            transition: all 0.3s ease;
        }

        .dot.active {
            width: 22px;
            border-radius: 20px;
            background: #ce1212;
        }

        .arrow-side {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: #ffffff;
            color: #ce1212;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.10);
            z-index: 5;
            transition: all 0.25s ease;
        }

        .arrow-side:hover {
            background: #ce1212;
            color: #fff;
            transform: translateY(-50%) scale(1.05);
        }

        .arrow-left {
            left: -22px;
        }

        .arrow-right {
            right: -22px;
        }

        .role-panel {
            min-height: 410px;
            transition: opacity 0.45s ease, transform 0.45s ease;
        }

        .role-panel.animating-out-left {
            opacity: 0.75;
            transform: translateX(-14px);
        }

        .role-panel.animating-out-right {
            opacity: 0.75;
            transform: translateX(14px);
        }

        .role-panel.animating-in-left {
            opacity: 0.85;
            transform: translateX(14px);
        }

        .role-panel.animating-in-right {
            opacity: 0.85;
            transform: translateX(-14px);
        }

        .role-kicker {
            color: #ce1212;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 12px;
            min-height: 19px;
        }

        .form-label-custom {
            font-weight: 500;
            font-size: 0.9rem;
            color: #374151;
            margin-bottom: 7px;
            display: block;
        }

        .form-control,
        .form-select {
            height: 46px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding-left: 14px;
            font-size: 0.93rem;
            box-shadow: none;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #ce1212;
            box-shadow: 0 0 0 0.2rem rgba(206, 18, 18, 0.08);
        }

        .password-field {
            position: relative;
        }

        .password-rules {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            z-index: 30;
            display: none;
            padding: 10px 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid rgba(206, 18, 18, 0.14);
            box-shadow: 0 18px 40px rgba(31, 41, 55, 0.14);
            backdrop-filter: blur(8px);
            animation: passwordRulesIn 0.2s ease;
        }

        .password-rules.show {
            display: block;
        }

        @keyframes passwordRulesIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .password-rules-title {
            font-size: 12px;
            font-weight: 800;
            color: #4b2a2a;
            margin-bottom: 8px;
        }

        .password-rules-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 8px;
        }

        .password-rule {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
            color: #8a6f6f;
            line-height: 1.3;
            transition: 0.25s ease;
        }

        .password-rule i {
            font-size: 13px;
            color: #c7a2a2;
            flex-shrink: 0;
        }

        .password-rule.valid {
            color: #157347;
            font-weight: 700;
        }

        .password-rule.valid i {
            color: #157347;
        }

        .password-rule.invalid {
            color: #8a6f6f;
        }

        .action-row {
            display: flex;
            gap: 10px;
            margin-top: 4px;
            min-height: 48px;
        }

        .btn-main,
        .btn-soft {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.25s ease;
        }

        .btn-main {
            border: none;
            background: linear-gradient(90deg, #ce1212, #e74c3c);
            color: #fff;
        }

        .btn-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(206, 18, 18, 0.18);
        }

        .btn-soft {
            border: 1px solid rgba(206, 18, 18, 0.18);
            background: #fff;
            color: #ce1212;
        }

        .btn-soft:hover {
            background: #fff7f7;
        }

        .signin-link {
            text-align: center;
            margin-top: 16px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .signin-link a {
            color: #ce1212;
            font-weight: 600;
            text-decoration: none;
        }

        .signin-link a:hover {
            text-decoration: underline;
        }

        .error-box {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
        }

        .captcha-box {
            margin: 0 0 14px;
            padding: 12px;
            border: 1px solid rgba(206, 18, 18, 0.14);
            background: #fffafa;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 8px 18px rgba(206, 18, 18, 0.04);
        }

        .captcha-message {
            display: none;
            margin: 10px auto 0;
            max-width: 330px;
            padding: 11px 14px;
            border-radius: 14px;
            background: linear-gradient(135deg, #fff5f5, #fffafa);
            border: 1px solid rgba(206, 18, 18, 0.18);
            color: #8f1d1d;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.45;
            box-shadow: 0 8px 20px rgba(206, 18, 18, 0.07);
            animation: captchaShake 0.35s ease;
        }

        .captcha-message.show {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .captcha-message i {
            font-size: 17px;
            color: #ce1212;
            flex-shrink: 0;
        }

        @keyframes captchaShake {
            0% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-4px);
            }

            50% {
                transform: translateX(4px);
            }

            75% {
                transform: translateX(-3px);
            }

            100% {
                transform: translateX(0);
            }
        }

        .modal-backdrop-custom {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 18px;
        }

        .modal-backdrop-custom.show {
            display: flex;
        }

        .request-modal {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.18);
            overflow: hidden;
            animation: modalFade 0.25s ease;
        }

        @keyframes modalFade {
            from {
                opacity: 0;
                transform: translateY(14px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-head {
            padding: 20px 22px 8px;
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 10px;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .modal-text {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.55;
            margin: 0;
        }

        .modal-close {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: #f8fafc;
            color: #6b7280;
            font-size: 1.2rem;
        }

        .modal-body-custom {
            padding: 8px 22px 22px;
        }

        .modal-body-custom textarea.form-control {
            min-height: 100px;
            resize: none;
            padding-top: 12px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }

        .helper-ok {
            display: none;
            margin-top: 10px;
            font-size: 0.86rem;
            color: #166534;
            font-weight: 600;
            min-height: 20px;
        }

        .helper-ok.show {
            display: block;
        }

        @media (max-width: 991px) {

            .signup-image,
            .signup-form {
                min-height: 300px;
            }

            .signup-image {
                border-radius: 24px 24px 0 0;
            }

            .signup-form {
                min-height: auto;
                padding: 24px 20px;
            }

            .arrow-side {
                width: 44px;
                height: 44px;
                top: auto;
                bottom: -18px;
                transform: none;
            }

            .arrow-side:hover {
                transform: scale(1.05);
            }

            .arrow-left {
                left: calc(50% - 54px);
            }

            .arrow-right {
                right: calc(50% - 54px);
            }

            .image-overlay h2 {
                font-size: 2.3rem;
            }

            .action-row,
            .modal-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            .captcha-box {
                align-items: flex-start;
                overflow-x: auto;
            }

            .password-rules-grid {
                grid-template-columns: 1fr;
            }
        }

        .site-popup {
            position: fixed;
            top: 25px;
            right: 25px;
            background: white;
            border-left: 5px solid #ce1212;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            border-radius: 14px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 9999;
            max-width: 360px;
        }

        .site-popup-icon {
            font-size: 24px;
        }

        .site-popup-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
            color: #333;
        }

        .site-popup-content strong {
            font-size: 15px;
        }

        .site-popup-content span {
            font-size: 13px;
            color: #666;
        }

        .site-popup-close {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #777;
        }

        .email-popup {
            position: fixed;
            top: 25px;
            right: 25px;
            background: #ffffff;
            border-left: 5px solid #ce1212;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.18);
            border-radius: 14px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 999999;
            max-width: 380px;
        }

        .email-popup-icon {
            font-size: 24px;
        }

        .email-popup-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .email-popup-content strong {
            font-size: 15px;
            color: #1f2937;
        }

        .email-popup-content span {
            font-size: 13px;
            color: #6b7280;
        }

        .email-popup-close {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #777;
        }
    </style>
</head>

<body>
    <?php if (isset($_GET['email_confirmation']) && $_GET['email_confirmation'] === 'sent'): ?>
        <div class="email-popup" id="emailPopup">
            <div class="email-popup-icon">✉️</div>

            <div class="email-popup-content">
                <strong>Check your email</strong>
                <span>We sent you a confirmation link. Please confirm your account before signing in.</span>
            </div>

            <button type="button" class="email-popup-close" onclick="document.getElementById('emailPopup').remove()">×</button>
        </div>
    <?php endif; ?>

    <main class="signup-section">
        <div class="container signup-shell">
            <button type="button" class="arrow-side arrow-left" id="prevRole" aria-label="Previous role">
                <i class="bi bi-chevron-left"></i>
            </button>

            <button type="button" class="arrow-side arrow-right" id="nextRole" aria-label="Next role">
                <i class="bi bi-chevron-right"></i>
            </button>

            <div class="row g-0 signup-wrapper">
                <div class="col-lg-5 signup-image" id="signupImage">
                    <div class="image-overlay">
                        <span class="image-badge" id="imageBadge">Client</span>
                        <h2 id="imageTitle">Eat smarter.</h2>
                        <p id="imageText">Start with a simple and balanced experience.</p>
                    </div>
                </div>

                <div class="col-lg-7 signup-form">
                    <div class="form-inner">
                        <a href="../index.php" class="back-home">← Back to Home</a>

                        <div class="mini-top">
                            <span class="badge-mini" id="topBadge">Client</span>
                            <div class="dots">
                                <span class="dot" id="dot-client"></span>
                                <span class="dot" id="dot-coach"></span>
                                <span class="dot" id="dot-nutritionist"></span>
                            </div>
                        </div>

                        <?php if ($error !== ''): ?>
                            <div class="error-box"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <div class="role-panel" id="rolePanel">
                            <div class="role-kicker" id="roleKicker">Create account</div>

                            <form method="POST" id="signupForm">
                                <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($selectedRole) ?>">
                                <input type="hidden" name="experience" id="experienceInput" value="<?= htmlspecialchars($_POST['experience'] ?? '') ?>">
                                <input type="hidden" name="speciality" id="specialityInput" value="<?= htmlspecialchars($_POST['speciality'] ?? '') ?>">
                                <input type="hidden" name="motivation" id="motivationInput" value="<?= htmlspecialchars($_POST['motivation'] ?? '') ?>">
                                <input type="hidden" name="request_form_completed" id="requestFormCompleted" value="<?= (($selectedRole === 'coach' || $selectedRole === 'nutritionist') && trim($_POST['experience'] ?? '') !== '' && trim($_POST['speciality'] ?? '') !== '' && trim($_POST['motivation'] ?? '') !== '') ? '1' : '0' ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-custom">First Name</label>
                                        <input type="text" name="prenom" class="form-control" placeholder="Enter your first name" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-custom">Last Name</label>
                                        <input type="text" name="nom" class="form-control" placeholder="Enter your last name" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-custom">Date of Birth</label>
                                        <input type="text" name="date_naissance" class="form-control" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-custom">Gender</label>
                                        <select name="sexe" class="form-select">
                                            <option value="">Select your gender</option>
                                            <option value="Female" <?= (($_POST['sexe'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                                            <option value="Male" <?= (($_POST['sexe'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-custom">Email Address</label>
                                        <input type="text" name="email" class="form-control" placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 mb-4 password-field">
                                        <label class="form-label-custom">Password</label>
                                        <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control" placeholder="Create a secure password">

                                        <div class="password-rules" id="passwordRules">
                                            <div class="password-rules-title">Password must contain:</div>

                                            <div class="password-rules-grid">
                                                <div class="password-rule invalid" id="ruleLength">
                                                    <i class="bi bi-circle"></i>
                                                    <span>8 characters</span>
                                                </div>

                                                <div class="password-rule invalid" id="ruleLetters">
                                                    <i class="bi bi-circle"></i>
                                                    <span>4 letters</span>
                                                </div>

                                                <div class="password-rule invalid" id="ruleNumber">
                                                    <i class="bi bi-circle"></i>
                                                    <span>1 number</span>
                                                </div>

                                                <div class="password-rule invalid" id="ruleSymbol">
                                                    <i class="bi bi-circle"></i>
                                                    <span>1 symbol</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="captcha-box">
                                    <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey) ?>"></div>

                                    <div class="captcha-message" id="captchaMessage">
                                        <i class="bi bi-shield-exclamation"></i>
                                        <span>Please confirm that you are not a robot.</span>
                                    </div>
                                </div>

                                <div class="action-row">
                                    <button type="submit" class="btn-main" id="submitBtn">Create Account</button>
                                    <button type="button" class="btn-soft" id="requiredFormBtn" style="visibility:hidden; opacity:0; pointer-events:none;">Required Form</button>
                                </div>

                                <div class="helper-ok" id="helperOk">Required form completed.</div>

                                <div class="signin-link">
                                    Already have an account?
                                    <a href="./signin.php">Sign In</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal-backdrop-custom" id="requestModal">
        <div class="request-modal">
            <div class="modal-head">
                <div>
                    <div class="modal-title" id="modalTitle">Coach Form</div>
                    <p class="modal-text" id="modalText">Complete this short form before continuing.</p>
                </div>
                <button type="button" class="modal-close" id="closeModal">&times;</button>
            </div>

            <div class="modal-body-custom">
                <div class="mb-3">
                    <label class="form-label-custom">Years of Experience</label>
                    <input type="text" class="form-control" id="modalExperience" value="<?= htmlspecialchars($_POST['experience'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Speciality</label>
                    <input type="text" class="form-control" id="modalSpeciality" value="<?= htmlspecialchars($_POST['speciality'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Why do you want to join?</label>
                    <textarea class="form-control" id="modalMotivation"><?= htmlspecialchars($_POST['motivation'] ?? '') ?></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-soft" id="cancelModalBtn">Cancel</button>
                    <button type="button" class="btn-main" id="saveModalBtn">Save Form</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-backdrop-custom" id="pendingSuccessModal">
        <div class="request-modal">
            <div class="modal-head">
                <div>
                    <div class="modal-title">Request Submitted Successfully</div>
                    <p class="modal-text">
                        Your account request has been received and is currently pending admin approval.
                        You will be able to sign in once your request has been reviewed and accepted.
                    </p>
                </div>
                <button type="button" class="modal-close" id="closePendingSuccessModal">&times;</button>
            </div>

            <div class="modal-body-custom">
                <div class="modal-actions">
                    <a href="../index.php" class="btn-soft text-center text-decoration-none d-flex align-items-center justify-content-center">
                        Back to Home
                    </a>
                    <a href="./signin.php" class="btn-main text-center text-decoration-none d-flex align-items-center justify-content-center">
                        Go to Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script>
        const roleData = [{
                key: 'client',
                badge: 'Client',
                imageBadge: 'Client',
                imageTitle: 'Eat smarter.',
                imageText: 'Start with a simple and balanced experience.',
                image: '../assets/img/reservation.jpg',
                kicker: 'Create account',
                submitText: 'Create Account',
                showRequiredButton: false
            },
            {
                key: 'coach',
                badge: 'Coach',
                imageBadge: 'Coach',
                imageTitle: 'Guide progress.',
                imageText: 'Support healthier routines with clarity and consistency.',
                image: '../assets/img/coach.jpg',
                kicker: 'Become a coach',
                submitText: 'Send Request',
                showRequiredButton: true
            },
            {
                key: 'nutritionist',
                badge: 'Nutritionist',
                imageBadge: 'Nutritionist',
                imageTitle: 'Share expertise.',
                imageText: 'Bring thoughtful nutrition support into the experience.',
                image: '../assets/img/nutritionist.jpg',
                kicker: 'Become a nutritionist',
                submitText: 'Send Request',
                showRequiredButton: true
            }
        ];

        let currentIndex = roleData.findIndex(role => role.key === "<?= htmlspecialchars($selectedRole) ?>");
        if (currentIndex === -1) {
            currentIndex = 0;
        }

        const rolePanel = document.getElementById('rolePanel');
        const topBadge = document.getElementById('topBadge');
        const imageBadge = document.getElementById('imageBadge');
        const imageTitle = document.getElementById('imageTitle');
        const imageText = document.getElementById('imageText');
        const roleKicker = document.getElementById('roleKicker');
        const submitBtn = document.getElementById('submitBtn');
        const requiredFormBtn = document.getElementById('requiredFormBtn');
        const roleInput = document.getElementById('roleInput');
        const helperOk = document.getElementById('helperOk');
        const signupImage = document.getElementById('signupImage');

        const experienceInput = document.getElementById('experienceInput');
        const specialityInput = document.getElementById('specialityInput');
        const motivationInput = document.getElementById('motivationInput');
        const requestFormCompleted = document.getElementById('requestFormCompleted');

        const requestModal = document.getElementById('requestModal');
        const closeModal = document.getElementById('closeModal');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        const saveModalBtn = document.getElementById('saveModalBtn');

        const modalTitle = document.getElementById('modalTitle');
        const modalText = document.getElementById('modalText');
        const modalExperience = document.getElementById('modalExperience');
        const modalSpeciality = document.getElementById('modalSpeciality');
        const modalMotivation = document.getElementById('modalMotivation');

        const pendingSuccessModal = document.getElementById('pendingSuccessModal');
        const closePendingSuccessModal = document.getElementById('closePendingSuccessModal');

        function updateDots(roleKey) {
            document.getElementById('dot-client').classList.toggle('active', roleKey === 'client');
            document.getElementById('dot-coach').classList.toggle('active', roleKey === 'coach');
            document.getElementById('dot-nutritionist').classList.toggle('active', roleKey === 'nutritionist');
        }

        function openPendingSuccessModal() {
            if (pendingSuccessModal) {
                pendingSuccessModal.classList.add('show');
            }
        }

        function closePendingSuccess() {
            if (pendingSuccessModal) {
                pendingSuccessModal.classList.remove('show');
            }
        }

        function openModal() {
            const currentRole = roleData[currentIndex];

            modalTitle.textContent = currentRole.key === 'coach' ? 'Coach Form' : 'Nutritionist Form';
            modalText.textContent = currentRole.key === 'coach' ?
                'Complete this short form before sending your coach request.' :
                'Complete this short form before sending your nutritionist request.';

            requestModal.classList.add('show');
        }

        function closeRequestModal() {
            requestModal.classList.remove('show');
        }

        function applyRole() {
            const role = roleData[currentIndex];

            topBadge.textContent = role.badge;
            imageBadge.textContent = role.imageBadge;
            imageTitle.textContent = role.imageTitle;
            imageText.textContent = role.imageText;
            roleKicker.textContent = role.kicker;
            submitBtn.textContent = role.submitText;
            roleInput.value = role.key;

            signupImage.style.backgroundImage =
                `linear-gradient(rgba(40, 26, 20, 0.16), rgba(40, 26, 20, 0.34)), url('${role.image}')`;

            if (role.showRequiredButton) {
                requiredFormBtn.style.visibility = 'visible';
                requiredFormBtn.style.opacity = '1';
                requiredFormBtn.style.pointerEvents = 'auto';
            } else {
                requiredFormBtn.style.visibility = 'hidden';
                requiredFormBtn.style.opacity = '0';
                requiredFormBtn.style.pointerEvents = 'none';
                helperOk.classList.remove('show');
            }

            if ((role.key === 'coach' || role.key === 'nutritionist') && requestFormCompleted.value === '1') {
                helperOk.classList.add('show');
            } else {
                helperOk.classList.remove('show');
            }

            updateDots(role.key);
        }

        function animateChange(direction) {
            rolePanel.classList.remove('animating-in-left', 'animating-in-right', 'animating-out-left', 'animating-out-right');
            signupImage.classList.remove('animating-in-left', 'animating-in-right', 'animating-out-left', 'animating-out-right');

            rolePanel.classList.add(direction === 'next' ? 'animating-out-left' : 'animating-out-right');
            signupImage.classList.add(direction === 'next' ? 'animating-out-left' : 'animating-out-right');

            setTimeout(() => {
                currentIndex = direction === 'next' ?
                    (currentIndex + 1) % roleData.length :
                    (currentIndex - 1 + roleData.length) % roleData.length;

                applyRole();

                rolePanel.classList.remove('animating-out-left', 'animating-out-right');
                signupImage.classList.remove('animating-out-left', 'animating-out-right');

                rolePanel.classList.add(direction === 'next' ? 'animating-in-right' : 'animating-in-left');
                signupImage.classList.add(direction === 'next' ? 'animating-in-right' : 'animating-in-left');

                setTimeout(() => {
                    rolePanel.classList.remove('animating-in-left', 'animating-in-right');
                    signupImage.classList.remove('animating-in-left', 'animating-in-right');
                }, 450);
            }, 180);
        }

        document.getElementById('nextRole').addEventListener('click', function() {
            animateChange('next');
        });

        document.getElementById('prevRole').addEventListener('click', function() {
            animateChange('prev');
        });

        requiredFormBtn.addEventListener('click', openModal);
        closeModal.addEventListener('click', closeRequestModal);
        cancelModalBtn.addEventListener('click', closeRequestModal);

        requestModal.addEventListener('click', function(e) {
            if (e.target === requestModal) {
                closeRequestModal();
            }
        });

        if (closePendingSuccessModal) {
            closePendingSuccessModal.addEventListener('click', closePendingSuccess);
        }

        if (pendingSuccessModal) {
            pendingSuccessModal.addEventListener('click', function(e) {
                if (e.target === pendingSuccessModal) {
                    closePendingSuccess();
                }
            });
        }

        saveModalBtn.addEventListener('click', function() {
            const experience = modalExperience.value.trim();
            const speciality = modalSpeciality.value.trim();
            const motivation = modalMotivation.value.trim();

            if (experience === '' || speciality === '' || motivation === '') {
                alert('Please fill in all required form fields.');
                return;
            }

            experienceInput.value = experience;
            specialityInput.value = speciality;
            motivationInput.value = motivation;
            requestFormCompleted.value = '1';
            helperOk.classList.add('show');

            closeRequestModal();
        });

        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const currentRole = roleData[currentIndex];

            roleInput.value = currentRole.key;

            if ((currentRole.key === 'coach' || currentRole.key === 'nutritionist') && requestFormCompleted.value !== '1') {
                e.preventDefault();
                openModal();
            }
        });

        <?php if (isset($_GET['request']) && $_GET['request'] === 'pending'): ?>
            openPendingSuccessModal();
        <?php endif; ?>

        applyRole();
    </script>

    <script>
        const signupForm = document.getElementById('signupForm');
        const captchaMessage = document.getElementById('captchaMessage');

        if (signupForm) {
            signupForm.addEventListener('submit', function(e) {
                const captchaResponse = typeof grecaptcha !== 'undefined' ? grecaptcha.getResponse() : '';

                if (captchaResponse.length === 0) {
                    e.preventDefault();

                    captchaMessage.classList.remove('show');
                    void captchaMessage.offsetWidth;
                    captchaMessage.classList.add('show');
                }
            });
        }
    </script>

    <script>
        const passwordInput = document.getElementById('mot_de_passe');
        const passwordRules = document.getElementById('passwordRules');

        const ruleLength = document.getElementById('ruleLength');
        const ruleLetters = document.getElementById('ruleLetters');
        const ruleNumber = document.getElementById('ruleNumber');
        const ruleSymbol = document.getElementById('ruleSymbol');

        function updateRule(ruleElement, isValid) {
            const icon = ruleElement.querySelector('i');

            if (isValid) {
                ruleElement.classList.add('valid');
                ruleElement.classList.remove('invalid');
                icon.className = 'bi bi-check-circle-fill';
            } else {
                ruleElement.classList.remove('valid');
                ruleElement.classList.add('invalid');
                icon.className = 'bi bi-circle';
            }
        }

        function updatePasswordRules() {
            const password = passwordInput.value;

            const lettersCount = (password.match(/[a-zA-Z]/g) || []).length;
            const hasNumber = /[0-9]/.test(password);
            const hasSymbol = /[^a-zA-Z0-9]/.test(password);

            updateRule(ruleLength, password.length >= 8);
            updateRule(ruleLetters, lettersCount >= 4);
            updateRule(ruleNumber, hasNumber);
            updateRule(ruleSymbol, hasSymbol);
        }

        if (passwordInput && passwordRules) {
            passwordInput.addEventListener('focus', function() {
                passwordRules.classList.add('show');
                updatePasswordRules();
            });

            passwordInput.addEventListener('input', updatePasswordRules);

            passwordInput.addEventListener('blur', function() {
                setTimeout(function() {
                    passwordRules.classList.remove('show');
                }, 120);
            });
        }
    </script>
    <?php if (isset($_GET['email_confirmation']) && $_GET['email_confirmation'] === 'sent'): ?>
        <div class="site-popup" id="emailPopup">
            <div class="site-popup-icon">
                ✉️
            </div>

            <div class="site-popup-content">
                <strong>Check your email</strong>
                <span>We sent you a confirmation link. Please open your email and confirm your account.</span>
            </div>

            <button type="button" class="site-popup-close" onclick="document.getElementById('emailPopup').style.display='none'">×</button>
        </div>
    <?php endif; ?>

</body>

</html>