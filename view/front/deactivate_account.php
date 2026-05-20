<?php
require_once 'auth.php';
require_once '../../config.php';

$userId = $_SESSION['user_id'];



$nom = '';
$prenom = '';
$email = '';
$role = '';
$profilePicture = 'default.png';

try {
    $pdo = config::getConnexion();

    // FIRST: if form submitted, DELETE user and redirect to signin
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sqlDelete = "DELETE FROM user WHERE id = :id";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->execute(['id' => $userId]);

        session_unset();
        session_destroy();
        header("Location: signin.php");
        exit();
    }

    // THEN: fetch user info for display
    $sql = "SELECT * FROM user WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if ($user) {
        $nom            = $user['nom'];
        $prenom         = $user['prenom'];
        $email          = $user['email'];
        $role           = $user['role'];
        $profilePicture = $user['profile_picture'] ?? 'default.png';
    } else {
        header("Location: ../../index.php");
        exit();
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<?php require_once __DIR__ . '/header.php'; ?>
<link href="../assets/css/main.css" rel="stylesheet">
<style>
        body {
            background: #f8f8f8;
        }

        /* Override main.css global section rules */
        section.profile-hero,
        section.profile-wrapper {
            overflow: visible !important;
        }
        section.profile-hero {
            padding: 55px 0 25px !important;
        }
        section.profile-wrapper {
            padding: 0 0 60px !important;
        }

        .profile-hero {
            background: linear-gradient(135deg, #fff 0%, #fff5f5 100%);
            position: relative;
            overflow: visible;
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
            overflow: visible;
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
            .profile-title { font-size: 32px; }
            .profile-card { padding: 28px 20px; }
            .info-grid { grid-template-columns: 1fr; }
            .profile-actions { flex-direction: column; }
            .profile-btn { justify-content: center; width: 100%; }
        }
    </style>

    <section class="profile-hero text-center">
        <div class="container">
            <h1 class="profile-title">Delete Account</h1>
            <p class="profile-subtitle">
                Permanently delete your account and all associated data.
            </p>
            <div class="profile-title-line"></div>
        </div>
    </section>

    <section class="profile-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="profile-card">

                        <div class="profile-top">
                            <img
                                src="../assets/img/profiles/<?= htmlspecialchars($profilePicture) ?>"
                                alt="Profile Picture"
                                style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:4px solid #fff;box-shadow:0 10px 25px rgba(206,18,18,0.25);margin-bottom:18px;">
                            <h2 class="profile-name"><?= htmlspecialchars($prenom . ' ' . $nom) ?></h2>
                            <span class="profile-role"><?= htmlspecialchars($role) ?></span>
                        </div>

                        <div class="warning-box">
                            <div class="warning-title">
                                <i class="bi bi-exclamation-diamond-fill me-2"></i>
                                Before you continue
                            </div>
                            <div>
                                This action will <strong>permanently delete</strong> your account and all associated data. This cannot be undone. You will be redirected to the sign in page.
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

                        <form method="POST" action="deactivate_account.php">

                            <div class="profile-actions">
                                <button type="submit" class="profile-btn btn-confirm-deactivate">
                                    <i class="bi bi-trash3-fill"></i>
                                    Delete My Account
                                </button>
                            </div>

                        </form>

                        <a href="profile.php" class="profile-btn btn-home-profile">
                            <i class="bi bi-arrow-left-circle"></i>
                            Cancel
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

</body>

</html>