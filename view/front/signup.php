<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UserController.php';

$controller = new UserController();
$error = '';

function isValidDateFormat($date)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $parts = explode('-', $date);
    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}

function isValidAllowedEmail($email)
{
    return preg_match('/^[A-Za-z0-9._%+-]+@(gmail\.com|esprit\.tn)$/', $email);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');
    $sexe = trim($_POST['sexe'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');

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
    } else {
        $cleanData = [
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => $date_naissance,
            'email' => $email,
            'mot_de_passe' => $mot_de_passe,
            'role' => 'client',
            'statut' => 'active',
            'sexe' => $sexe
        ];

        try {
            $controller->store($cleanData);

            $newUser = $controller->findByEmail($email);

            if ($newUser) {
                header('Location: index.php?signup=success&id=' . urlencode((string)$newUser['id']));
                exit;
            }

            header('Location: ../index.php?signup=success');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
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
            background: linear-gradient(135deg, #fdf6f6, #ffffff, #f7fbfb);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .signup-section {
            padding: 80px 0;
            display: flex;
            align-items: center;
            min-height: 100vh;
        }

        .signup-wrapper {
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.10);
            background: #fff;
        }

        .signup-image {
            background: linear-gradient(rgba(120, 95, 70, 0.18), rgba(70, 50, 35, 0.35)), url('../assets/img/reservation.jpg');
            background-size: cover;
            background-position: center;
            padding: 50px;
            display: flex;
            align-items: flex-end;
            color: white;
            min-height: 100%;
        }

        .signup-image h2 {
            font-weight: 700;
            font-size: 2rem;
            line-height: 1.3;
            margin-bottom: 12px;
            color: #fff;
        }

        .signup-image p {
            font-size: 0.98rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 0;
        }

        .signup-form {
            padding: 50px 40px;
        }

        .badge-mini {
            background: rgba(206, 18, 18, 0.08);
            color: #ce1212;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 15px;
        }

        .signup-title {
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 10px;
            color: #212529;
        }

        .signup-desc {
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 0.96rem;
            line-height: 1.7;
        }

        .form-label-custom {
            font-weight: 500;
            font-size: 0.92rem;
            color: #495057;
            margin-bottom: 8px;
            display: block;
        }

        .form-control,
        .form-select {
            height: 50px;
            border-radius: 12px;
            border: 1px solid #e3e3e3;
            padding-left: 14px;
            font-size: 0.95rem;
            box-shadow: none;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #ce1212;
            box-shadow: 0 0 0 0.2rem rgba(206, 18, 18, 0.10);
        }

        .btn-signup {
            width: 100%;
            height: 52px;
            border-radius: 12px;
            background: linear-gradient(90deg, #ce1212, #e74c3c);
            border: none;
            color: white;
            font-weight: 700;
            transition: all 0.25s ease;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(206, 18, 18, 0.18);
        }

        .signin-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.92rem;
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

        .back-home {
            color: #ce1212;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }

        .back-home:hover {
            text-decoration: underline;
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

        @media (max-width: 991px) {
            .signup-form {
                padding: 35px 24px;
            }

            .signup-image {
                min-height: 300px;
            }

            .signup-image h2 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>

<body>

    <main class="signup-section">
        <div class="container">
            <div class="row g-0 signup-wrapper">

                <div class="col-lg-5 signup-image">
                    <div>
                        <h2>Support your goals.<br>Shape your wellness journey.</h2>
                        <p>Join a space designed for users, coaches, and nutrition professionals to build healthier routines together.</p>
                    </div>
                </div>

                <div class="col-lg-7 signup-form">

                    <a href="../index.php" class="back-home">
                        ← Back to Home
                    </a>

                    <span class="badge-mini">Smart Meal Planner</span>

                    <h1 class="signup-title">Create Your Account</h1>
                    <p class="signup-desc">
                        Get started by entering your information.<br>
                        Welcome to a smarter, healthier way to eat.
                    </p>

                    <?php if ($error !== ''): ?>
                        <div class="error-box"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
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
                                <input
                                    type="text"
                                    name="date_naissance"
                                    class="form-control"
                                    placeholder="YYYY-MM-DD"
                                    value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
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

                            <div class="col-md-6 mb-4">
                                <label class="form-label-custom">Password</label>
                                <input type="password" name="mot_de_passe" class="form-control" placeholder="Create a secure password">
                            </div>

                        </div>

                        <button type="submit" class="btn-signup">Create Account</button>

                        <div class="signin-link">
                            Already have an account?
                            <a href="./signin.php">Sign In</a>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </main>

</body>

</html>