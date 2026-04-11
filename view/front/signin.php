<?php
require_once '../../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $pdo = config::getConnexion();

        $sql = "SELECT * FROM user WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            if ($password === $user['mot_de_passe']) {
                if ($user['statut'] === 'banned') {
                    $error = "Your account has been banned.";
                } elseif ($user['statut'] === 'deactivated') {
                    header("Location: ../index.php?deactivated=1&id=" . urlencode($user['id']));
                    exit();
                } else {
                    header("Location: index.php?id=" . urlencode($user['id']) . "&login=success");
                    exit();
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    } catch (Exception $e) {
        $error = "An error occurred. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Smart Meal Planner</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8eeee, #fcf8f8);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .signin-page {
            width: 100%;
            max-width: 1080px;
            min-height: 620px;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 0.8fr 1.2fr;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.10);
        }

        .visual-side {
            position: relative;
            background:
                linear-gradient(rgba(0, 0, 0, 0.18), rgba(0, 0, 0, 0.18)),
                url('https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;
            display: flex;
            align-items: flex-end;
            padding: 22px;
        }

        .visual-overlay {
            width: 100%;
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 18px;
            padding: 20px;
            color: #fff;
        }

        .visual-overlay h1 {
            font-size: 28px;
            line-height: 1.2;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .visual-overlay p {
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.95);
            max-width: 380px;
        }

        .form-side {
            position: relative;
            padding: 40px 52px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fffdfd;
        }

        .back-home {
            position: absolute;
            top: 24px;
            left: 30px;
            text-decoration: none;
            color: #9b2c2c;
            font-size: 14px;
            font-weight: 600;
            transition: 0.25s ease;
        }

        .back-home:hover {
            color: #6f1d1b;
            transform: translateX(-2px);
        }

        .brand {
            display: inline-block;
            margin-bottom: 16px;
            color: #c0392b;
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 12px;
            text-transform: uppercase;
        }

        .form-box {
            max-width: 410px;
            width: 100%;
            margin: 0 auto;
        }

        .form-box h2 {
            font-size: 36px;
            color: #2d1414;
            margin-bottom: 10px;
            line-height: 1.1;
        }

        .subtitle {
            color: #776262;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .input-group {
            margin-bottom: 18px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #4b2a2a;
            font-weight: 600;
        }

        .input-group input {
            width: 100%;
            height: 52px;
            padding: 0 16px;
            border-radius: 14px;
            border: 1px solid #eddcdc;
            background: #fff;
            font-size: 14px;
            color: #2d1414;
            outline: none;
            transition: 0.25s ease;
        }

        .input-group input:focus {
            border-color: #c0392b;
            box-shadow: 0 0 0 4px rgba(192, 57, 43, 0.10);
        }

        .forgot-wrap {
            display: flex;
            justify-content: flex-end;
            margin: 4px 0 22px;
        }

        .forgot-password {
            text-decoration: none;
            color: #c0392b;
            font-size: 13px;
            font-weight: 600;
            transition: 0.25s ease;
        }

        .forgot-password:hover {
            color: #922b21;
            text-decoration: underline;
        }

        .signin-btn {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #dc3545, #b02a37);
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s ease;
            box-shadow: 0 12px 28px rgba(176, 42, 55, 0.24);
        }

        .signin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(176, 42, 55, 0.28);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 24px 0;
            color: #bea6a6;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #f0dfdf;
        }

        .signup-link {
            text-align: center;
            color: #776262;
            font-size: 14px;
            line-height: 1.6;
        }

        .signup-link a {
            color: #c0392b;
            text-decoration: none;
            font-weight: 700;
            margin-left: 4px;
        }

        .signup-link a:hover {
            text-decoration: underline;
            color: #922b21;
        }

        .error-box {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 13px 15px;
            border-radius: 14px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: 600;
        }

        @media (max-width: 950px) {
            .signin-page {
                grid-template-columns: 1fr;
            }

            .visual-side {
                min-height: 230px;
            }

            .form-side {
                padding: 36px 24px 28px;
            }

            .back-home {
                top: 20px;
                left: 24px;
            }

            .form-box h2 {
                font-size: 32px;
            }
        }

        @media (max-width: 520px) {
            body {
                padding: 14px;
            }

            .signin-page {
                border-radius: 18px;
            }

            .visual-side {
                padding: 18px;
            }

            .visual-overlay h1 {
                font-size: 25px;
            }

            .form-box h2 {
                font-size: 27px;
            }

            .input-group input,
            .signin-btn {
                height: 50px;
            }
        }
    </style>
</head>

<body>

    <div class="signin-page">

        <div class="visual-side">
            <div class="visual-overlay">
                <h1>Smarter meals start with you.</h1>
                <p>
                    Sign in to continue your journey toward healthier choices and a more personalized nutrition experience.
                </p>
            </div>
        </div>

        <div class="form-side">
            <a href="../index.php" class="back-home">← Back to Home</a>

            <div class="form-box">
                <span class="brand">Smart Meal Planner</span>
                <h2>Welcome back
                             


                </h2>

                <?php if ($error !== ''): ?>
                    <div class="error-box"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="input-group">
                        <label for="email">Email address</label>
                        <input type="text" id="email" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"autocomplete="off">
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="new-password">
                    </div>

                    <div class="forgot-wrap">
                        <a href="#" class="forgot-password">Forgot your password?</a>
                    </div>

                    <button type="submit" class="signin-btn">Sign In</button>
                </form>

                <div class="divider">or</div>

                <div class="signup-link">
                    New here?
                    <a href="signup.php">Create an account</a>
                </div>
            </div>
        </div>

    </div>

</body>

</html>