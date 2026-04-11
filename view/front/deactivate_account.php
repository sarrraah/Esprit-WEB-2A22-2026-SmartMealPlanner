<?php
require_once '../../config.php';

$userId = $_GET['id'] ?? '';

if ($userId == '') {
    header("Location: ../index.php");
    exit();
}

$nom = '';
$prenom = '';
$email = '';
$role = '';

try {
    $pdo = config::getConnexion();

    // FIRST: if form submitted, deactivate and redirect immediately
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sqlUpdate = "UPDATE user SET statut = :statut WHERE id = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([
            'statut' => 'deactivated',
            'id' => $userId
        ]);

        header("Location: ../index.php?deactivated=1&id=" . urlencode($userId));
        exit();
    }

    // THEN: fetch user info for display
    $sql = "SELECT * FROM user WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if ($user) {
        $nom = $user['nom'];
        $prenom = $user['prenom'];
        $email = $user['email'];
        $role = $user['role'];
    } else {
        header("Location: ../../index.php");
        exit();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Deactivate Account</title>

    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">

    <style>
        body {
            background: #f8f8f8;
        }

        .profile-hero {
            padding: 55px 0 25px;
            background: linear-gradient(135deg, #fff 0%, #fff5f5 100%);
            position: relative;
            overflow: hidden;
        }

        .profile-hero::before {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            background: rgba(206, 18, 18, 0.08);
            border-radius: 50%;
            top: -60px;
            right: -60px;
        }

        .profile-hero::after {
            content: "";
            position: absolute;
            width: 160px;
            height: 160px;
            background: rgba(206, 18, 18, 0.05);
            border-radius: 50%;
            bottom: -50px;
            left: -40px;
        }

        .profile-title {
            font-size: 36px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 6px;
        }

        .profile-subtitle {
            color: #6c757d;
            font-size: 16px;
            max-width: 650px;
            margin: 0 auto;
        }

        .profile-title-line {
            width: 80px;
            height: 4px;
            background: #ce1212;
            border-radius: 999px;
            margin: 18px auto 0;
        }

        .profile-wrapper {
            margin-bottom: 60px;
            position: relative;
            z-index: 2;
        }

        .profile-card {
            background: #fff;
            border-radius: 24px;
            padding: 40px 35px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
            border: 1px solid rgba(0, 0, 0, 0.04);
        }

        .profile-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 45px rgba(206, 18, 18, 0.12);
        }

        .profile-top {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ce1212, #e75b5b);
            color: #fff;
            font-size: 32px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(206, 18, 18, 0.25);
            margin-bottom: 18px;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 6px;
        }

        .profile-role {
            display: inline-block;
            padding: 8px 16px;
            background: rgba(206, 18, 18, 0.08);
            color: #ce1212;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
        }

        .warning-box {
            background: #fff5f5;
            border: 1px solid rgba(220, 53, 69, 0.18);
            color: #7a1f1f;
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .warning-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #dc3545;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 24px;
        }

        .info-box {
            background: #fafafa;
            border-radius: 18px;
            padding: 18px 20px;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .info-box:hover {
            background: #fff5f5;
            border-color: rgba(206, 18, 18, 0.18);
            transform: translateY(-3px);
        }

        .info-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #212529;
            word-break: break-word;
        }

        .reason-box {
            background: #fafafa;
            border-radius: 18px;
            padding: 18px 20px;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .reason-box textarea {
            border-radius: 14px;
            border: 1px solid #ddd;
            padding: 12px 14px;
            font-size: 15px;
            box-shadow: none;
            resize: none;
            min-height: 130px;
        }

        .profile-actions {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .profile-btn {
            border: none;
            border-radius: 999px;
            padding: 13px 24px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-confirm-deactivate {
            background: #dc3545;
            color: #fff;
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.2);
        }

        .btn-confirm-deactivate:hover {
            background: #bb2d3b;
            color: #fff;
            transform: translateY(-3px);
        }

        .btn-home-profile {
            background: #fff;
            color: #212529;
            border: 1px solid #ddd;
        }

        .btn-home-profile:hover {
            background: #f5f5f5;
            color: #ce1212;
            border-color: #ce1212;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .profile-title {
                font-size: 32px;
            }

            .profile-card {
                padding: 28px 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .profile-actions {
                flex-direction: column;
            }

            .profile-btn {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container position-relative d-flex align-items-center justify-content-between">

            <a href="index.php?id=<?= urlencode($userId) ?>&login=success" class="logo d-flex align-items-center me-auto me-xl-0">
                <h1 class="sitename">Yummy</h1>
                <span>.</span>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.php?id=<?= urlencode($userId) ?>&login=success#hero" class="active">Home</a></li>
                    <li><a href="index.php?id=<?= urlencode($userId) ?>&login=success#about">About</a></li>
                    <li><a href="index.php?id=<?= urlencode($userId) ?>&login=success#menu">Menu</a></li>
                    <li><a href="index.php?id=<?= urlencode($userId) ?>&login=success#contact">Contact</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <a href="profile.php?id=<?= urlencode($userId) ?>" class="btn-book-a-table text-start" style="line-height: 1.3;">
                <div>
                    <strong><?= htmlspecialchars($prenom . ' ' . $nom) ?></strong>
                </div>
            </a>

        </div>
    </header>

    <section class="profile-hero text-center">
        <div class="container" data-aos="fade-up">
            <h1 class="profile-title">Deactivate Account</h1>
            <p class="profile-subtitle">
                You can temporarily deactivate your account and return to the public homepage.
            </p>
            <div class="profile-title-line"></div>
        </div>
    </section>

    <section class="profile-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="zoom-in" data-aos-delay="100">
                    <div class="profile-card">

                        <div class="profile-top">
                            <div class="profile-avatar">
                                <?= htmlspecialchars(strtoupper(substr($prenom, 0, 1))) ?>
                            </div>
                            <h2 class="profile-name"><?= htmlspecialchars($prenom . ' ' . $nom) ?></h2>
                            <span class="profile-role"><?= htmlspecialchars($role) ?></span>
                        </div>

                        <div class="warning-box">
                            <div class="warning-title">
                                <i class="bi bi-exclamation-diamond-fill me-2"></i>
                                Before you continue
                            </div>
                            <div>
                                Deactivating your account will temporarily disable your access. You can reactivate your account at any time by logging back in.
                            </div>
                        </div>

                        <div class="info-grid">
                            <div class="info-box">
                                <span class="info-label">Full Name</span>
                                <div class="info-value"><?= htmlspecialchars($prenom . ' ' . $nom) ?></div>
                            </div>

                            <div class="info-box">
                                <span class="info-label">Email Address</span>
                                <div class="info-value"><?= htmlspecialchars($email) ?></div>
                            </div>
                        </div>

                        <form method="POST" action="deactivate_account.php?id=<?= urlencode($userId) ?>">

                            <div class="profile-actions">
                                <button type="submit" class="profile-btn btn-confirm-deactivate">
                                    <i class="bi bi-person-x-fill"></i>
                                    Confirm Deactivation
                                </button>
                            </div>

                        </form>

                        <a href="profile.php?id=<?= urlencode($userId) ?>" class="profile-btn btn-home-profile">
                            <i class="bi bi-arrow-left-circle"></i>
                            Cancel
                        </a>
                    </div>
                    </form>

                </div>
            </div>
        </div>
        </div>
    </section>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/aos/aos.js"></script>
    <script>
        AOS.init({
            duration: 900,
            once: true
        });
    </script>

</body>

</html>