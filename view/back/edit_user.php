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

function normalizeDateForInput($date)
{
    $date = trim((string)$date);

    if ($date === '') {
        return '';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }

    $timestamp = strtotime($date);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    return '';
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die('Missing user ID.');
}

$user = $controller->show($id);

if (!$user) {
    die('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = trim($_POST['mot_de_passe'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $statut = trim($_POST['statut'] ?? $user['statut']);
    $sexe = trim($_POST['sexe'] ?? '');

    if (
        $nom === '' ||
        $prenom === '' ||
        $date_naissance === '' ||
        $email === '' ||
        $role === '' ||
        $statut === '' ||
        $sexe === ''
    ) {
        $error = 'Please fill in all required fields.';
    } elseif (!isValidDateFormat($date_naissance)) {
        $error = 'Date of birth must be a valid date.';
    } elseif (!isValidAllowedEmail($email)) {
        $error = 'Email must end with @gmail.com or @esprit.tn.';
    } else {
        $cleanData = [
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => $date_naissance,
            'email' => $email,
            'mot_de_passe' => $mot_de_passe,
            'role' => $role,
            'statut' => $statut,
            'sexe' => $sexe
        ];

        try {
            $controller->update($id, $cleanData);
            header('Location: users.php');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$currentNom = $_POST['nom'] ?? ($user['nom'] ?? '');
$currentPrenom = $_POST['prenom'] ?? ($user['prenom'] ?? '');
$currentDate = $_POST['date_naissance'] ?? normalizeDateForInput($user['date_naissance'] ?? '');
$currentEmail = $_POST['email'] ?? ($user['email'] ?? '');
$currentRole = $_POST['role'] ?? ($user['role'] ?? '');
$currentStatus = $_POST['statut'] ?? ($user['statut'] ?? '');
$currentSexe = $_POST['sexe'] ?? trim((string)($user['sexe'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(220, 38, 38, 0.10), transparent 24%),
                radial-gradient(circle at bottom right, rgba(239, 68, 68, 0.08), transparent 26%),
                linear-gradient(135deg, #fafbff 0%, #f8fafc 45%, #ffffff 100%);
            color: #1f2937;
            padding: 28px;
        }

        .page-shell {
            max-width: 920px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            margin-bottom: 22px;
        }

        .topbar h1 {
            font-size: 38px;
            line-height: 1.05;
            color: #111827;
            margin-bottom: 8px;
        }

        .topbar p {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
            max-width: 640px;
        }

        .back-btn {
            display: inline-block;
            text-decoration: none;
            background: #ffffff;
            color: #b91c1c;
            padding: 13px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #fecaca;
            box-shadow: 0 10px 20px rgba(17, 24, 39, 0.05);
            transition: 0.25s ease;
            white-space: nowrap;
        }

        .back-btn:hover {
            background: #fff7f7;
            transform: translateY(-2px);
        }

        .form-card {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 28px;
            padding: 26px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.07);
        }

        .error-box {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 13px 15px;
            border-radius: 14px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: bold;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #374151;
        }

        .form-control {
            width: 100%;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: 0.2s ease;
        }

        .form-control:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.08);
        }

        .hint {
            margin-top: 7px;
            font-size: 12px;
            color: #9ca3af;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-secondary {
            text-decoration: none;
            background: #ffffff;
            color: #374151;
            border: 1px solid #e5e7eb;
            padding: 13px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.2s ease;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-primary {
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #ffffff;
            padding: 13px 20px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 14px 28px rgba(185, 28, 28, 0.16);
            transition: 0.25s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            opacity: 0.96;
        }

        @media (max-width: 768px) {
            body {
                padding: 18px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar h1 {
                font-size: 30px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary,
            .back-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <div class="page-shell">

        <div class="topbar">
            <div>
                <h1>Edit User</h1>
                <p>Update the selected account information.</p>
            </div>

            <a href="users.php" class="back-btn">← Back to Users</a>
        </div>

        <div class="form-card">
            <?php if ($error !== ''): ?>
                <div class="error-box"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom">Last Name</label>
                        <input id="nom" class="form-control" type="text" name="nom" value="<?= htmlspecialchars($currentNom) ?>" placeholder="Enter last name">
                    </div>

                    <div class="form-group">
                        <label for="prenom">First Name</label>
                        <input id="prenom" class="form-control" type="text" name="prenom" value="<?= htmlspecialchars($currentPrenom) ?>" placeholder="Enter first name">
                    </div>

                    <div class="form-group">
                        <label for="date_naissance">Date of Birth</label>
                        <input id="date_naissance" class="form-control" type="text" name="date_naissance" value="<?= htmlspecialchars($currentDate) ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" class="form-control" type="text" name="email" value="<?= htmlspecialchars($currentEmail) ?>" placeholder="example@gmail.com">
                        <span class="hint">Allowed domains: @gmail.com or @esprit.tn</span>
                    </div>

                    <div class="form-group">
                        <label for="mot_de_passe">New Password</label>
                        <input id="mot_de_passe" class="form-control" type="password" name="mot_de_passe" value="" placeholder="Leave blank to keep current password">
                        <span class="hint">Leave empty if you do not want to change it.</span>
                    </div>

                    <div class="form-group">
                        <label for="sexe">Gender</label>
                        <select id="sexe" class="form-control" name="sexe">
                            <option value="">Select gender</option>
                            <option value="Female" <?= (strtolower($currentSexe) === 'female') ? 'selected' : '' ?>>Female</option>
                            <option value="Male" <?= (strtolower($currentSexe) === 'male') ? 'selected' : '' ?>>Male</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" class="form-control" name="role">
                            <option value="">Select role</option>
                            <option value="client" <?= ($currentRole === 'client') ? 'selected' : '' ?>>Client</option>
                            <option value="coach" <?= ($currentRole === 'coach') ? 'selected' : '' ?>>Coach</option>
                            <option value="nutritionist" <?= ($currentRole === 'nutritionist') ? 'selected' : '' ?>>Nutritionist</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="statut">Status</label>
                        <select id="statut" class="form-control" name="statut">
                            <option value="">Select status</option>
                            <option value="active" <?= ($currentStatus === 'active') ? 'selected="selected"' : '' ?>>Active</option>
                            <option value="banned" <?= ($currentStatus === 'banned') ? 'selected="selected"' : '' ?>>Banned</option>
                            <option value="deactivated" <?= ($currentStatus === 'deactivated') ? 'selected="selected"' : '' ?>>Deactivated</option>
                            <option value="pending" <?= ($currentStatus === 'pending') ? 'selected="selected"' : '' ?>>Pending</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="users.php" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Update User</button>
                </div>
            </form>
        </div>

    </div>

</body>

</html>