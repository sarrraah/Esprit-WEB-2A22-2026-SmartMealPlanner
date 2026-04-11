<?php
require_once '../../config.php';

$userId = $_GET['id'] ?? '';
$editMode = isset($_GET['edit']) && $_GET['edit'] == '1';

if ($userId == '') {
    die("No user ID provided.");
}

$nom = '';
$prenom = '';
$email = '';
$sexe = '';
$date_naissance = '';
$role = '';
$statut = '';

try {
    $pdo = config::getConnexion();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sexe = trim($_POST['sexe'] ?? '');
        $date_naissance = trim($_POST['date_naissance'] ?? '');

        $sqlUpdate = "UPDATE user 
                      SET nom = :nom,
                          prenom = :prenom,
                          email = :email,
                          sexe = :sexe,
                          date_naissance = :date_naissance
                      WHERE id = :id";

        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'sexe' => $sexe,
            'date_naissance' => $date_naissance,
            'id' => $userId
        ]);

        header("Location: profile.php?id=" . urlencode($userId));
        exit();
    }

    $sql = "SELECT * FROM user WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if ($user) {
        $nom = $user['nom'];
        $prenom = $user['prenom'];
        $email = $user['email'];
        $sexe = $user['sexe'];
        $date_naissance = $user['date_naissance'];
        $role = $user['role'];
        $statut = $user['statut'];
    } else {
        die("User not found.");
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
    <title>My Profile</title>

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
            max-width: 600px;
            margin: 0 auto;
        }

        .profile-title-line {
            width: 80px;
            height: 4px;
            background: #ce1212;
            border-radius: 999px;
            margin: 18px auto 0;
            transition: 0.3s ease;
        }

        .profile-wrapper {
            margin-top: 0;
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
            margin-bottom: 35px;
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
            transition: transform 0.3s ease;
        }

        .profile-card:hover .profile-avatar {
            transform: scale(1.06);
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

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-top: 30px;
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

        .info-value input,
        .info-value select {
            border-radius: 12px;
            border: 1px solid #ddd;
            padding: 10px 12px;
            font-size: 15px;
            font-weight: 500;
            box-shadow: none;
        }

        .info-value input:focus,
        .info-value select:focus {
            border-color: #ce1212;
            box-shadow: 0 0 0 0.15rem rgba(206, 18, 18, 0.12);
        }

        .status-badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-active {
            background: rgba(25, 135, 84, 0.12);
            color: #198754;
        }

        .status-banned {
            background: rgba(220, 53, 69, 0.12);
            color: #dc3545;
        }

        .status-deactivated {
            background: rgba(108, 117, 125, 0.14);
            color: #6c757d;
        }

        .profile-actions {
            margin-top: 35px;
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

        .btn-edit-profile {
            background: #ce1212;
            color: #fff;
            box-shadow: 0 10px 20px rgba(206, 18, 18, 0.2);
        }

        .btn-edit-profile:hover {
            background: #b51010;
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

        .btn-deactivate-profile {
            background: rgba(220, 53, 69, 0.08);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.18);
        }

        .btn-deactivate-profile:hover {
            background: #dc3545;
            color: #fff;
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
                    <strong><?= htmlspecialchars($prenom . ' ' . $nom) ?></strong><br>
                    <small>User ID: <?= htmlspecialchars($userId) ?></small>
                </div>
            </a>

        </div>
    </header>

    <section class="profile-hero text-center">
        <div class="container" data-aos="fade-up">
            <h1 class="profile-title">My Profile</h1>
            <p class="profile-subtitle">
                View your personal information, manage your account, and keep your profile up to date with ease.
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

                        <form method="POST" action="profile.php?id=<?= urlencode($userId) ?>">
                            <div class="info-grid">

                                <div class="info-box">
                                    <span class="info-label">First Name</span>
                                    <div class="info-value">
                                        <?php if ($editMode): ?>
                                            <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($prenom) ?>">
                                        <?php else: ?>
                                            <?= htmlspecialchars($prenom) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <span class="info-label">Last Name</span>
                                    <div class="info-value">
                                        <?php if ($editMode): ?>
                                            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($nom) ?>">
                                        <?php else: ?>
                                            <?= htmlspecialchars($nom) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <span class="info-label">Email Address</span>
                                    <div class="info-value">
                                        <?php if ($editMode): ?>
                                            <input type="text" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
                                        <?php else: ?>
                                            <?= htmlspecialchars($email) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <span class="info-label">Gender</span>
                                    <div class="info-value">
                                        <?php if ($editMode): ?>
                                            <select name="sexe" class="form-control">
                                                <option value="Female" <?= $sexe === 'Female' ? 'selected' : '' ?>>Female</option>
                                                <option value="Male" <?= $sexe === 'Male' ? 'selected' : '' ?>>Male</option>
                                            </select>
                                        <?php else: ?>
                                            <?= htmlspecialchars($sexe) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="info-box">
                                    <span class="info-label">Date of Birth</span>
                                    <div class="info-value">
                                        <?php if ($editMode): ?>
                                            <input type="text" name="date_naissance" class="form-control" value="<?= htmlspecialchars($date_naissance) ?>">
                                        <?php else: ?>
                                            <?= htmlspecialchars($date_naissance) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>

                            <div class="profile-actions">
                                <?php if ($editMode): ?>
                                    <button type="submit" class="profile-btn btn-edit-profile">
                                        <i class="bi bi-check2-circle"></i>
                                        Save Changes
                                    </button>

                                    <a href="profile.php?id=<?= urlencode($userId) ?>" class="profile-btn btn-home-profile">
                                        <i class="bi bi-x-circle"></i>
                                        Cancel
                                    </a>
                                <?php else: ?>
                                    <a href="profile.php?id=<?= urlencode($userId) ?>&edit=1" class="profile-btn btn-edit-profile">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit Profile
                                    </a>
                                <?php endif; ?>

                                <a href="deactivate_account.php?id=<?= urlencode($userId) ?>" class="profile-btn btn-deactivate-profile">
                                    <i class="bi bi-person-x"></i>
                                    Deactivate Account
                                </a>

                                <a href="index.php?id=<?= urlencode($userId) ?>&login=success" class="profile-btn btn-home-profile">
                                    <i class="bi bi-house-door"></i>
                                    Back to Home
                                </a>
                                <a href="logout.php" class="profile-btn btn-home-profile">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Log Out
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