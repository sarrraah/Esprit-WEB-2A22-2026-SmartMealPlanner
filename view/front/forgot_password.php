<?php
session_start();
require_once '../../config.php';

$error = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    $backUrl = 'profile.php';
} else {
    $backUrl = 'signin.php';
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    try {
        $pdo = config::getConnexion();

        $sql = "SELECT * FROM user WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "No account found with this email.";
        } elseif (empty($newPassword)) {
            $error = "Please enter a new password.";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $update = "UPDATE user SET mot_de_passe = :pwd WHERE email = :email";
            $stmt = $pdo->prepare($update);
            $stmt->execute([
                'pwd' => $hashedPassword,
                'email' => $email
            ]);

            $success = "Password updated successfully. You can now sign in.";
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
                    <h2 class="reset-title">Reset your password</h2>
                    <p class="reset-subtitle">Enter your email and choose a new password.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <input type="text" name="email" class="form-control" placeholder="Your email">
                        </div>

                        <div class="mb-3">
                            <input type="text" name="new_password" class="form-control" placeholder="New password">
                        </div>

                        <div class="text-center">
                            <button type="submit">Update Password</button>
                        </div>

                    </form>

                    <div class="text-center mt-4">
                        <a href="<?= htmlspecialchars($backUrl) ?>" class="back-link">Go Back</a>
                    </div>

                </div>

            </div>
        </section>

    </main>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>

</body>

</html>