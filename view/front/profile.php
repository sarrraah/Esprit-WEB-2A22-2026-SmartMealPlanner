<?php
require_once 'auth.php';
require_once '../../config.php';

$userId = $_SESSION['user_id'];
$editMode = isset($_GET['edit']) && $_GET['edit'] == '1';

$nom = '';
$prenom = '';
$email = '';
$sexe = '';
$date_naissance = '';
$role = '';
$statut = '';
$experience = '';
$speciality = '';
$motivation = '';
$reapplyError = '';

try {
    $pdo = config::getConnexion();

    // Reapply request form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reapply_request'])) {
        $requestedRole = strtolower(trim($_POST['requested_role'] ?? ''));
        $newExperience = trim($_POST['new_experience'] ?? '');
        $newSpeciality = trim($_POST['new_speciality'] ?? '');
        $newMotivation = trim($_POST['new_motivation'] ?? '');

        if (!in_array($requestedRole, ['coach', 'nutritionist'], true)) {
            $reapplyError = 'Please select a valid role.';
        } elseif ($newExperience === '' || $newSpeciality === '' || $newMotivation === '') {
            $reapplyError = 'Please complete all request fields.';
        } else {
            $stmtReapply = $pdo->prepare("
                UPDATE user
                SET role = :role,
                    statut = 'pending',
                    experience = :experience,
                    speciality = :speciality,
                    motivation = :motivation
                WHERE id = :id
            ");

            $stmtReapply->execute([
                'role' => $requestedRole,
                'experience' => $newExperience,
                'speciality' => $newSpeciality,
                'motivation' => $newMotivation,
                'id' => $userId
            ]);

            header("Location: profile.php?request=pending");
            exit();
        }
    }

    // Main profile update form
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reapply_request'])) {
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

        header("Location: profile.php");
        exit();
    }

    $sql = "SELECT * FROM user WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $nom = $user['nom'];
        $prenom = $user['prenom'];
        $email = $user['email'];
        $sexe = $user['sexe'];
        $date_naissance = $user['date_naissance'];
        $role = $user['role'];
        $statut = $user['statut'];
        $experience = $user['experience'] ?? '';
        $speciality = $user['speciality'] ?? '';
        $motivation = $user['motivation'] ?? '';
    } else {
        die("User not found.");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$showPendingRequestBanner =
    isset($_GET['request']) &&
    $_GET['request'] === 'pending' &&
    strtolower(trim((string)$statut)) === 'pending';

/* Handle denied state before building display logic */
if (strtolower(trim((string)$statut)) === 'denied') {
    $pdo->prepare("
        UPDATE user
        SET role = 'client', statut = 'active'
        WHERE id = :id
    ")->execute(['id' => $userId]);

    $role = 'client';
    $statut = 'active';
}



/* Handle denied state before building display logic */
if (strtolower(trim((string)$statut)) === 'denied') {
    $pdo->prepare("
        UPDATE user
        SET role = 'client', statut = 'active'
        WHERE id = :id
    ")->execute(['id' => $userId]);

    $role = 'client';
    $statut = 'active';
}

/* Force pending banner state if redirected after reapply */
if ($showPendingRequestBanner) {
    $statut = 'pending';
}

$normalizedRole = strtolower(trim((string)$role));
$normalizedStatus = strtolower(trim((string)$statut));
$showRequestSection = ($normalizedStatus === 'pending');

$requestBoxClass = 'request-neutral';
$requestTitle = 'Account Status';
$requestText = 'Your account is active and ready to use.';

$requestBoxClass = 'request-pending';
$requestTitle = 'Professional Request Pending';
$requestText = 'Your professional account request is currently being reviewed by the admin team. You will be able to access professional features once it has been approved.';
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

        .request-banner {
            padding: 16px 18px;
            border-radius: 14px;
            margin-bottom: 24px;
            font-weight: 600;
            line-height: 1.7;
        }

        .request-banner-pending {
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
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

        .request-status-box {
            margin-top: 28px;
            border-radius: 20px;
            padding: 20px 22px;
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }

        .request-status-box:hover {
            transform: translateY(-2px);
        }

        .request-pending {
            background: #fff8e8;
            border-color: #fde3a7;
        }

        .request-neutral {
            background: #f8f9fb;
            border-color: #e9ecef;
        }

        .request-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .request-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            color: #212529;
        }

        .request-badge {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .request-badge.pending {
            background: rgba(255, 193, 7, 0.16);
            color: #b58100;
        }

        .request-badge.neutral {
            background: rgba(108, 117, 125, 0.12);
            color: #6c757d;
        }

        .request-text {
            font-size: 14px;
            line-height: 1.7;
            color: #5f6770;
            margin-bottom: 0;
        }

        .request-details {
            margin-top: 18px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .request-detail-box {
            background: rgba(255, 255, 255, 0.72);
            border-radius: 16px;
            padding: 15px 16px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .request-detail-box.full {
            grid-column: 1 / -1;
        }

        .request-detail-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #8a8f98;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 6px;
        }

        .request-detail-value {
            font-size: 15px;
            font-weight: 600;
            color: #212529;
            line-height: 1.65;
            word-break: break-word;
        }

        .reapply-card {
            margin-top: 26px;
            background: #ffffff;
            border: 1px solid #f0f0f0;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .reapply-title {
            font-size: 20px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 8px;
        }

        .reapply-text {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 18px;
        }

        .reapply-toggle-btn {
            border: none;
            border-radius: 999px;
            padding: 12px 18px;
            font-weight: 600;
            font-size: 14px;
            background: #ce1212;
            color: #fff;
            cursor: pointer;
            transition: 0.25s ease;
        }

        .reapply-toggle-btn:hover {
            background: #b51010;
            transform: translateY(-1px);
        }

        .reapply-hidden {
            display: none;
            margin-top: 18px;
        }

        .reapply-visible {
            display: block;
            margin-top: 18px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reapply-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 10px;
        }

        .reapply-field {
            display: flex;
            flex-direction: column;
        }

        .reapply-field.full {
            grid-column: 1 / -1;
        }

        .reapply-field label {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 7px;
        }

        .reapply-field input,
        .reapply-field select,
        .reapply-field textarea {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 11px 12px;
            font-size: 14px;
            outline: none;
            transition: 0.2s ease;
        }

        .reapply-field input:focus,
        .reapply-field select:focus,
        .reapply-field textarea:focus {
            border-color: #ce1212;
            box-shadow: 0 0 0 0.15rem rgba(206, 18, 18, 0.12);
        }

        .reapply-field textarea {
            min-height: 110px;
            resize: vertical;
        }

        .reapply-actions {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
        }

        .reapply-btn {
            border: none;
            border-radius: 999px;
            padding: 13px 22px;
            font-weight: 600;
            font-size: 15px;
            background: #ce1212;
            color: #fff;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(206, 18, 18, 0.2);
        }

        .reapply-btn:hover {
            background: #b51010;
            transform: translateY(-2px);
        }

        .reapply-error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 16px;
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

        .status-pending {
            background: rgba(255, 193, 7, 0.16);
            color: #b58100;
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

            .request-details,
            .info-grid,
            .reapply-grid {
                grid-template-columns: 1fr;
            }

            .request-detail-box.full,
            .reapply-field.full {
                grid-column: auto;
            }

            .profile-actions {
                flex-direction: column;
            }

            .profile-btn,
            .reapply-btn {
                justify-content: center;
                width: 100%;
            }

            .reapply-actions {
                justify-content: stretch;
            }
        }

        .request-banner,
        .alert-request {
            display: block;
            width: 100%;
            padding: 18px 22px;
            border-radius: 18px;
            margin-bottom: 28px;
            font-weight: 600;
            font-size: 15px;
            line-height: 1.7;
            border: 1px solid transparent;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .request-banner-pending {
            background: linear-gradient(135deg, #fffaf0 0%, #fff7ed 100%) !important;
            color: #c2410c !important;
            border: 1px solid #fed7aa !important;
            border-left: 5px solid #f59e0b !important;
        }

        .alert-accepted {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf3 100%) !important;
            color: #15803d !important;
            border: 1px solid #bbf7d0 !important;
            border-left: 5px solid #22c55e !important;
        }

        .alert-denied {
            background: linear-gradient(135deg, #fff1f2 0%, #fee2e2 100%) !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca !important;
            border-left: 5px solid #ef4444 !important;
        }
    </style>
</head>

<body>

    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container position-relative d-flex align-items-center justify-content-between">

            <a href="index.php?login=success" class="logo d-flex align-items-center me-auto me-xl-0">
                <h1 class="sitename">Yummy</h1>
                <span>.</span>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="index.php?login=success#hero" class="active">Home</a></li>
                    <li><a href="index.php?login=success#about">About</a></li>
                    <li><a href="index.php?login=success#menu">Menu</a></li>
                    <li><a href="index.php?login=success#contact">Contact</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

            <a href="profile.php" class="btn-book-a-table text-start" style="line-height: 1.3;">
                <div>
                    <strong><?= htmlspecialchars($prenom . ' ' . $nom) ?></strong><br>
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

                    <?php
                    $requestStatus = $_GET['request'] ?? null;

                    $showPendingRequestBanner = (
                        $requestStatus === 'pending' &&
                        $normalizedStatus === 'pending'
                    );

                    $showDeniedBanner = (
                        $normalizedRole === 'client' &&
                        $normalizedStatus === 'active' &&
                        (
                            trim((string)$experience) !== '' ||
                            trim((string)$speciality) !== '' ||
                            trim((string)$motivation) !== ''
                        )
                    );
                    ?>

                    <?php if ($showPendingRequestBanner): ?>
                        <div class="request-banner request-banner-pending">
                            Your professional request has been submitted successfully and is now pending admin review.
                        </div>
                    <?php elseif ($normalizedStatus === 'active' && ($normalizedRole === 'coach' || $normalizedRole === 'nutritionist')): ?>
                        <div class="alert-request alert-accepted">
                            Your professional request has been approved.
                        </div>
                    <?php elseif ($showDeniedBanner): ?>
                        <div class="alert-request alert-denied">
                            Your professional request was not approved. You can continue using your account as a client.
                        </div>
                    <?php endif; ?>

                    <div class="profile-card">

                        <div class="profile-top">
                            <div class="profile-avatar">
                                <?= htmlspecialchars(strtoupper(substr($prenom, 0, 1))) ?>
                            </div>
                            <h2 class="profile-name"><?= htmlspecialchars($prenom . ' ' . $nom) ?></h2>
                            <span class="profile-role"><?= htmlspecialchars($role) ?></span>
                        </div>

                        <?php if ($normalizedRole === 'client' && $normalizedStatus === 'active'): ?>
                            <div class="reapply-card">
                                <div class="reapply-title">Professional Account</div>
                                <div class="reapply-text">
                                    Want to join as a coach or nutritionist? Submit a request and the admin team will review it.
                                </div>

                                <button type="button" class="reapply-toggle-btn" id="toggleReapplyBtn">
                                    Apply for Professional Account
                                </button>

                                <div id="reapplyFormWrapper" class="reapply-hidden">
                                    <?php if ($reapplyError !== ''): ?>
                                        <div class="reapply-error"><?= htmlspecialchars($reapplyError) ?></div>
                                    <?php endif; ?>

                                    <form method="POST" action="profile.php">
                                        <input type="hidden" name="reapply_request" value="1">

                                        <div class="reapply-grid">
                                            <div class="reapply-field">
                                                <label for="requested_role">Requested Role</label>
                                                <select name="requested_role" id="requested_role">
                                                    <option value="">Select a role</option>
                                                    <option value="coach">Coach</option>
                                                    <option value="nutritionist">Nutritionist</option>
                                                </select>
                                            </div>

                                            <div class="reapply-field">
                                                <label for="new_experience">Years of Experience</label>
                                                <input type="text" name="new_experience" id="new_experience" placeholder="Enter your experience">
                                            </div>

                                            <div class="reapply-field">
                                                <label for="new_speciality">Speciality</label>
                                                <input type="text" name="new_speciality" id="new_speciality" placeholder="Enter your speciality">
                                            </div>

                                            <div class="reapply-field full">
                                                <label for="new_motivation">Motivation</label>
                                                <textarea name="new_motivation" id="new_motivation" placeholder="Tell us why you want to apply"></textarea>
                                            </div>
                                        </div>

                                        <div class="reapply-actions">
                                            <button type="submit" class="reapply-btn">Submit Request</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="profile.php">
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

                                <div class="info-box">
                                    <span class="info-label">Account Status</span>
                                    <div class="info-value">
                                        <?php
                                        $displayStatus = in_array($normalizedStatus, ['active', 'pending', 'banned', 'deactivated'], true)
                                            ? $normalizedStatus
                                            : 'active';
                                        ?>
                                        <span class="status-badge status-<?= htmlspecialchars($displayStatus) ?>">
                                            <?= htmlspecialchars(ucfirst($displayStatus)) ?>
                                        </span>
                                    </div>
                                </div>

                            </div>

                            <div class="profile-actions">
                                <?php if ($editMode): ?>
                                    <button type="submit" class="profile-btn btn-edit-profile">
                                        <i class="bi bi-check2-circle"></i>
                                        Save Changes
                                    </button>

                                    <a href="profile.php" class="profile-btn btn-home-profile">
                                        <i class="bi bi-x-circle"></i>
                                        Cancel
                                    </a>
                                <?php else: ?>
                                    <a href="profile.php?edit=1" class="profile-btn btn-edit-profile">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit Profile
                                    </a>

                                    <a href="forgot_password.php" class="profile-btn btn-deactivate-profile">
                                        <i class="bi bi-key"></i>
                                        Change Password
                                    </a>
                                <?php endif; ?>

                                <a href="deactivate_account.php" class="profile-btn btn-deactivate-profile">
                                    <i class="bi bi-person-x"></i>
                                    Deactivate Account
                                </a>

                                <a href="index.php?login=success" class="profile-btn btn-home-profile">
                                    <i class="bi bi-house-door"></i>
                                    Back to Home
                                </a>

                                <a href="logout.php" class="profile-btn btn-home-profile">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Log Out
                                </a>

                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <a href="../back/users.php" class="profile-btn btn-home-profile">
                                        <i class="bi bi-speedometer2"></i>
                                        Admin Dashboard
                                    </a>
                                <?php endif; ?>
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

        const toggleBtn = document.getElementById('toggleReapplyBtn');
        const formWrapper = document.getElementById('reapplyFormWrapper');

        if (toggleBtn && formWrapper) {
            toggleBtn.addEventListener('click', function() {
                if (formWrapper.classList.contains('reapply-visible')) {
                    formWrapper.classList.remove('reapply-visible');
                    formWrapper.classList.add('reapply-hidden');
                } else {
                    formWrapper.classList.remove('reapply-hidden');
                    formWrapper.classList.add('reapply-visible');
                }
            });
        }

        if (window.location.search.includes('request=')) {
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    </script>

</body>

</html>