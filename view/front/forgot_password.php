<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

$error = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    $backUrl = 'profile.php';
} else {
    $backUrl = 'signin.php';
}

$token = $_GET['token'] ?? '';

function sendPasswordResetEmail($email, $prenom, $token)
{
    $mail = new PHPMailer(true);

    try {
        $resetLink = "http://localhost:8080/smart-meal-planner/view/front/forgot_password.php?token=" . urlencode($token);

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

        $mail->Username = 'smartmealplanner0@gmail.com';
        $mail->Password = 'sadz adky luaw ckyt';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('smartmealplanner0@gmail.com', 'Smart Meal Planner');
        $mail->addAddress($email, $prenom);

        $mail->isHTML(true);
        $mail->Subject = 'Reset your Smart Meal Planner password';

        $mail->Body = "
            <h2>Hello $prenom,</h2>
            <p>You requested to reset your password.</p>
            <p>Click the button below to choose a new password:</p>

            <p>
                <a href='$resetLink'
                   style='background:#ce1212;color:white;padding:12px 20px;text-decoration:none;border-radius:8px;display:inline-block;'>
                    Reset Password
                </a>
            </p>

            <p>If the button does not work, copy this link:</p>
            <p>$resetLink</p>

            <p>This link will expire in 30 minutes.</p>
        ";

        $mail->send();
        return true;
    } catch (MailException $e) {
        die("Mailer Error: " . $mail->ErrorInfo);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        $pdo = config::getConnexion();

        if ($action === 'request_reset') {
            $email = trim($_POST['email'] ?? '');

            if ($email === '') {
                $error = "Please enter your email.";
            } else {
                $sql = "SELECT * FROM user WHERE email = :email LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'email' => $email
                ]);

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $error = "No account found with this email.";
                } else {
                    $resetToken = bin2hex(random_bytes(32));
                    $resetExpires = date('Y-m-d H:i:s', time() + (30 * 60));

                    $update = "UPDATE user
                               SET reset_token = :reset_token,
                                   reset_expires = :reset_expires
                               WHERE id = :id";

                    $stmt = $pdo->prepare($update);
                    $stmt->execute([
                        'reset_token' => $resetToken,
                        'reset_expires' => $resetExpires,
                        'id' => $user['id']
                    ]);

                    $prenom = $user['prenom'] ?? 'there';

                    $emailSent = sendPasswordResetEmail($email, $prenom, $resetToken);

                    if ($emailSent) {
                        $success = "We sent you a password reset link. Please check your email.";
                    } else {
                        $error = "Reset link created, but the email could not be sent.";
                    }
                }
            }
        }

        if ($action === 'update_password') {
            $resetToken = $_POST['token'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';

            $lettersCount = preg_match_all('/[a-zA-Z]/', $newPassword);
            $hasNumber = preg_match('/[0-9]/', $newPassword);
            $hasSymbol = preg_match('/[^a-zA-Z0-9]/', $newPassword);

            if ($resetToken === '') {
                $error = "Invalid reset link.";
            } elseif ($newPassword === '') {
                $error = "Please enter a new password.";
            } elseif (strlen($newPassword) < 8 || $lettersCount < 4 || !$hasNumber || !$hasSymbol) {
                $error = "Password must contain at least 8 characters, 4 letters, 1 number and 1 special character.";
            } else {
                $sql = "SELECT * FROM user
                        WHERE reset_token = :reset_token
                        AND reset_expires > NOW()
                        LIMIT 1";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'reset_token' => $resetToken
                ]);

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $error = "Invalid or expired reset link.";
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    $update = "UPDATE user
                               SET mot_de_passe = :pwd,
                                   reset_token = NULL,
                                   reset_expires = NULL,
                                   remember_token = NULL,
                                   remember_expires = NULL
                               WHERE id = :id";

                    $stmt = $pdo->prepare($update);
                    $stmt->execute([
                        'pwd' => $hashedPassword,
                        'id' => $user['id']
                    ]);

                    session_unset();
                    session_destroy();

                    header("Location: signin.php?password_reset=success");
                    exit;
                }
            }
        }
    } catch (Exception $e) {
        $error = "Something went wrong.";
    }
}

$isResetMode = false;

if ($token !== '') {
    try {
        $pdo = config::getConnexion();

        $sql = "SELECT * FROM user
                WHERE reset_token = :reset_token
                AND reset_expires > NOW()
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'reset_token' => $token
        ]);

        $resetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resetUser) {
            $isResetMode = true;
        } else {
            $error = "Invalid or expired reset link.";
        }
    } catch (Exception $e) {
        $error = "Something went wrong.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Reset Password - Smart Meal Planner</title>

    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #fff9f9 0%, #fff3f3 40%, #ffffff 100%);
        }

        .reset-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
        }

        .reset-wrapper {
            max-width: 520px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.96);
            padding: 38px 40px;
            border-radius: 22px;
            border: 1px solid rgba(206, 18, 18, 0.06);
            box-shadow: 0 14px 38px rgba(206, 18, 18, 0.07);
            transition: 0.25s ease;
        }

        .reset-wrapper:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 45px rgba(206, 18, 18, 0.10);
        }

        .reset-small-title {
            color: #ce1212;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
            text-align: center;
        }

        .reset-title {
            color: #2a2a2a;
            font-size: 26px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
        }

        .reset-subtitle {
            color: #8a7a7a;
            font-size: 14px;
            text-align: center;
            margin-bottom: 26px;
            line-height: 1.5;
        }

        .form-control {
            border-radius: 14px;
            padding: 13px 16px;
            border: 1px solid #f2dede;
            background: #fffefe;
            transition: 0.2s ease;
        }

        .form-control:focus {
            border-color: #ce1212;
            box-shadow: 0 0 0 4px rgba(206, 18, 18, 0.08);
        }

        button {
            border-radius: 50px;
            padding: 12px 32px;
            background: linear-gradient(135deg, #ce1212, #a50f0f);
            border: none;
            color: #fff;
            font-weight: 600;
            transition: 0.25s ease;
            box-shadow: 0 8px 22px rgba(206, 18, 18, 0.18);
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(206, 18, 18, 0.22);
        }

        .alert {
            border-radius: 12px;
            font-size: 14px;
            padding: 12px 16px;
        }

        .back-link {
            color: #ce1212;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .back-link:hover {
            color: #a50f0f;
            text-decoration: underline;
        }

        .password-rules {
            margin-top: 10px;
            padding: 12px 14px;
            border-radius: 14px;
            background: #fff7f7;
            border: 1px solid #f5d2d2;
            display: none;
        }

        .password-rules.show {
            display: block;
        }

        .password-rules-title {
            font-size: 13px;
            font-weight: 700;
            color: #5f4a4a;
            margin-bottom: 8px;
        }

        .password-rules-grid {
            display: grid;
            gap: 5px;
        }

        .password-rule {
            font-size: 13px;
            color: #9b8a8a;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: 0.2s ease;
        }

        .password-rule.valid {
            color: #198754;
        }

        .password-rule.invalid {
            color: #9b8a8a;
        }

        .password-rule i {
            font-size: 14px;
        }
    </style>
</head>

<body class="starter-page-page">

    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container position-relative d-flex align-items-center justify-content-center">
            <a href="../index.php" class="logo d-flex align-items-center">
                <h1 class="sitename">Smart Meal Planner</h1>
                <span>.</span>
            </a>
        </div>
    </header>

    <main class="main">

        <section class="section reset-section">
            <div class="container">

                <div class="reset-wrapper">

                    <div class="reset-small-title">Account Help</div>

                    <?php if ($isResetMode): ?>
                        <h2 class="reset-title">Choose new password</h2>
                        <p class="reset-subtitle">Enter your new password below.</p>
                    <?php else: ?>
                        <h2 class="reset-title">Reset your password</h2>
                        <p class="reset-subtitle">Enter your email. We will send you a secure reset link.</p>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <?php if ($isResetMode): ?>

                        <form method="POST">
                            <input type="hidden" name="action" value="update_password">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                            <div class="mb-3">
                                <input type="password" name="new_password" id="mot_de_passe" class="form-control" placeholder="New password">

                                <div class="password-rules" id="passwordRules">
                                    <div class="password-rules-title">Password must contain:</div>

                                    <div class="password-rules-grid">
                                        <div class="password-rule invalid" id="ruleLength">
                                            <i class="bi bi-circle"></i>
                                            <span>At least 8 characters</span>
                                        </div>

                                        <div class="password-rule invalid" id="ruleLetters">
                                            <i class="bi bi-circle"></i>
                                            <span>At least 4 letters</span>
                                        </div>

                                        <div class="password-rule invalid" id="ruleNumber">
                                            <i class="bi bi-circle"></i>
                                            <span>At least 1 number</span>
                                        </div>

                                        <div class="password-rule invalid" id="ruleSymbol">
                                            <i class="bi bi-circle"></i>
                                            <span>At least 1 special character</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit">Update Password</button>
                            </div>
                        </form>

                    <?php else: ?>

                        <form method="POST">
                            <input type="hidden" name="action" value="request_reset">

                            <div class="mb-3">
                                <input type="text" name="email" class="form-control" placeholder="Your email">
                            </div>

                            <div class="text-center">
                                <button type="submit">Send Reset Link</button>
                            </div>
                        </form>

                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link">Go Back</a>
                    </div>

                </div>

            </div>
        </section>

    </main>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        const passwordInput = document.getElementById('mot_de_passe');
        const passwordRules = document.getElementById('passwordRules');

        const ruleLength = document.getElementById('ruleLength');
        const ruleLetters = document.getElementById('ruleLetters');
        const ruleNumber = document.getElementById('ruleNumber');
        const ruleSymbol = document.getElementById('ruleSymbol');

        function updateRule(ruleElement, isValid) {
            if (!ruleElement) return;

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

</body>

</html>