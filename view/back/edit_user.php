<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UserController.php';
require_once 'admin_auth.php';

$controller = new UserController();
$error = '';

function isValidDateFormat(string $date): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $parts = explode('-', $date);
    return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
}
function isValidAllowedEmail(string $email): bool
{
    return preg_match('/^[A-Za-z0-9._%+-]+@(gmail\.com|esprit\.tn)$/', $email);
}

function normalizeDateForInput(string $date): string
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
    $newPassword = trim($_POST['mot_de_passe'] ?? '');
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
        $error = 'Please fill in the required fields.';
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
            'role' => $role,
            'statut' => $statut,
            'sexe' => $sexe
        ];

        /*
            Password rule:
            - Empty field = keep old password.
            - Filled field = update password.
            No HTML5 validation is used.
        */
        if ($newPassword !== '') {
            $cleanData['mot_de_passe'] = $newPassword;
        }

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

    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --red: #dc2626;
            --red-dark: #991b1b;
            --red-soft: #fff7f7;
            --bg: #f8fafc;
            --bg-2: #ffffff;
            --card: rgba(255, 255, 255, 0.88);
            --card-solid: #ffffff;
            --border: rgba(255, 255, 255, 0.78);
            --border-strong: #fecaca;
            --input-border: #e5e7eb;
            --text: #111827;
            --text-2: #374151;
            --muted: #6b7280;
            --hint: #9ca3af;
            --shadow: 0 18px 42px rgba(15, 23, 42, 0.07);
            --shadow-hover: 0 24px 60px rgba(15, 23, 42, 0.11);
            --input-bg: #ffffff;
            --error-bg: #fef2f2;
            --error-text: #b91c1c;
        }

        body.dark-mode {
            --bg: #0f172a;
            --bg-2: #111827;
            --card: rgba(17, 24, 39, 0.88);
            --card-solid: #111827;
            --border: rgba(255, 255, 255, 0.08);
            --border-strong: rgba(248, 113, 113, 0.28);
            --input-border: rgba(248, 113, 113, 0.22);
            --text: #f9fafb;
            --text-2: #e5e7eb;
            --muted: #9ca3af;
            --hint: #9ca3af;
            --shadow: 0 24px 70px rgba(0, 0, 0, 0.28);
            --shadow-hover: 0 30px 82px rgba(0, 0, 0, 0.38);
            --input-bg: #0f172a;
            --error-bg: rgba(127, 29, 29, 0.35);
            --error-text: #fecaca;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(220, 38, 38, 0.10), transparent 24%),
                radial-gradient(circle at bottom right, rgba(239, 68, 68, 0.08), transparent 26%),
                linear-gradient(135deg, var(--bg) 0%, var(--bg-2) 100%);
            color: var(--text-2);
            padding: 28px;
            transition: background 0.25s ease, color 0.25s ease;
        }

        .page-shell {
            max-width: 920px;
            margin: 0 auto;
            animation: pageFade 0.45s ease both;
        }

        @keyframes pageFade {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 22px;
        }

        .topbar h1 {
            font-size: 38px;
            line-height: 1.05;
            color: var(--text);
            margin-bottom: 8px;
            letter-spacing: -0.8px;
        }

        .topbar p {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.6;
            max-width: 640px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mini-tools {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px;
            border-radius: 999px;
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .mini-btn {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid var(--border-strong);
            background: var(--card-solid);
            color: var(--red);
            cursor: pointer;
            font-size: 15px;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.22s ease;
        }

        .mini-btn:hover {
            transform: translateY(-2px);
            background: var(--red-soft);
            box-shadow: 0 12px 22px rgba(185, 28, 28, 0.12);
        }

        body.dark-mode .mini-btn:hover {
            background: rgba(248, 113, 113, 0.12);
        }

        .mini-btn.active {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: #ffffff;
            border-color: transparent;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            text-decoration: none;
            background: var(--card-solid);
            color: #b91c1c;
            padding: 13px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid var(--border-strong);
            box-shadow: 0 10px 20px rgba(17, 24, 39, 0.05);
            transition: 0.25s ease;
            white-space: nowrap;
        }

        body.dark-mode .back-btn {
            color: #fca5a5;
        }

        .back-btn:hover {
            background: var(--red-soft);
            transform: translateY(-2px);
        }

        body.dark-mode .back-btn:hover {
            background: rgba(248, 113, 113, 0.12);
        }

        .form-card {
            background: var(--card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 26px;
            box-shadow: var(--shadow);
            transition: 0.25s ease;
        }

        .form-card:hover {
            box-shadow: var(--shadow-hover);
        }

        .error-box {
            background: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--border-strong);
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
            color: var(--text-2);
        }

        .form-control {
            width: 100%;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            border-radius: 14px;
            padding: 13px 14px;
            font-size: 14px;
            color: var(--text);
            outline: none;
            transition: 0.2s ease;
        }

        .form-control:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.08);
            transform: translateY(-1px);
        }

        .form-control::placeholder {
            color: var(--hint);
        }

        .hint {
            margin-top: 7px;
            font-size: 12px;
            color: var(--hint);
            line-height: 1.45;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-secondary {
            text-decoration: none;
            background: var(--card-solid);
            color: var(--text-2);
            border: 1px solid var(--input-border);
            padding: 13px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.2s ease;
        }

        .btn-secondary:hover {
            background: var(--red-soft);
            transform: translateY(-2px);
        }

        body.dark-mode .btn-secondary:hover {
            background: rgba(248, 113, 113, 0.12);
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

            .topbar-actions {
                width: 100%;
                justify-content: flex-start;
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
                <h1 data-i18n="title">Edit User</h1>
                <p data-i18n="subtitle">Update the selected account information.</p>
            </div>

            <div class="topbar-actions">
                <div class="mini-tools">
                    <button type="button" id="themeToggle" class="mini-btn" title="Dark / Light">
                        <i class="bi bi-moon-stars"></i>
                    </button>

                    <button type="button" id="langEnBtn" class="mini-btn active">
                        EN
                    </button>

                    <button type="button" id="langFrBtn" class="mini-btn">
                        FR
                    </button>
                </div>

                <a href="users.php" class="back-btn">
                    <span>←</span>
                    <span data-i18n="backToUsers">Back to Users</span>
                </a>
            </div>
        </div>

        <div class="form-card">
            <?php if ($error !== ''): ?>
                <div class="error-box"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom" data-i18n="lastName">Last Name</label>
                        <input
                            id="nom"
                            class="form-control"
                            type="text"
                            name="nom"
                            value="<?= htmlspecialchars($currentNom) ?>"
                            data-placeholder-en="Enter last name"
                            data-placeholder-fr="Entrer le nom"
                            placeholder="Enter last name">
                    </div>

                    <div class="form-group">
                        <label for="prenom" data-i18n="firstName">First Name</label>
                        <input
                            id="prenom"
                            class="form-control"
                            type="text"
                            name="prenom"
                            value="<?= htmlspecialchars($currentPrenom) ?>"
                            data-placeholder-en="Enter first name"
                            data-placeholder-fr="Entrer le prénom"
                            placeholder="Enter first name">
                    </div>

                    <div class="form-group">
                        <label for="date_naissance" data-i18n="dob">Date of Birth</label>
                        <input
                            id="date_naissance"
                            class="form-control"
                            type="text"
                            name="date_naissance"
                            value="<?= htmlspecialchars($currentDate) ?>"
                            data-placeholder-en="YYYY-MM-DD"
                            data-placeholder-fr="AAAA-MM-JJ"
                            placeholder="YYYY-MM-DD">
                    </div>

                    <div class="form-group">
                        <label for="email" data-i18n="email">Email</label>
                        <input
                            id="email"
                            class="form-control"
                            type="text"
                            name="email"
                            value="<?= htmlspecialchars($currentEmail) ?>"
                            data-placeholder-en="example@gmail.com"
                            data-placeholder-fr="exemple@gmail.com"
                            placeholder="example@gmail.com">
                        <span class="hint" data-i18n="emailHint">Allowed domains: @gmail.com or @esprit.tn</span>
                    </div>

                    <div class="form-group">
                        <label for="mot_de_passe" data-i18n="newPassword">New Password</label>
                        <input
                            id="mot_de_passe"
                            class="form-control"
                            type="text"
                            name="mot_de_passe"
                            value=""
                            data-placeholder-en="Write a new password only if you want to change it"
                            data-placeholder-fr="Écrivez un nouveau mot de passe seulement si vous voulez le changer"
                            placeholder="Write a new password only if you want to change it">
                        <span class="hint" data-i18n="passwordHint">The current password is encrypted and cannot be displayed.</span>
                    </div>

                    <div class="form-group">
                        <label for="sexe" data-i18n="gender">Gender</label>
                        <select id="sexe" class="form-control" name="sexe">
                            <option value="" data-i18n="selectGender">Select gender</option>
                            <option value="Female" <?= (strtolower($currentSexe) === 'female') ? 'selected' : '' ?> data-i18n="female">Female</option>
                            <option value="Male" <?= (strtolower($currentSexe) === 'male') ? 'selected' : '' ?> data-i18n="male">Male</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="role" data-i18n="role">Role</label>
                        <select id="role" class="form-control" name="role">
                            <option value="" data-i18n="selectRole">Select role</option>
                            <option value="client" <?= ($currentRole === 'client') ? 'selected' : '' ?> data-i18n="client">Client</option>
                            <option value="coach" <?= ($currentRole === 'coach') ? 'selected' : '' ?> data-i18n="coach">Coach</option>
                            <option value="nutritionist" <?= ($currentRole === 'nutritionist') ? 'selected' : '' ?> data-i18n="nutritionist">Nutritionist</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="statut" data-i18n="status">Status</label>
                        <select id="statut" class="form-control" name="statut">
                            <option value="" data-i18n="selectStatus">Select status</option>
                            <option value="active" <?= ($currentStatus === 'active') ? 'selected="selected"' : '' ?> data-i18n="active">Active</option>
                            <option value="banned" <?= ($currentStatus === 'banned') ? 'selected="selected"' : '' ?> data-i18n="banned">Banned</option>
                            <option value="deactivated" <?= ($currentStatus === 'deactivated') ? 'selected="selected"' : '' ?> data-i18n="deactivated">Deactivated</option>
                            <option value="pending" <?= ($currentStatus === 'pending') ? 'selected="selected"' : '' ?> data-i18n="pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="users.php" class="btn-secondary" data-i18n="cancel">Cancel</a>
                    <button type="submit" class="btn-primary" data-i18n="updateUser">Update User</button>
                </div>
            </form>
        </div>

    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const langEnBtn = document.getElementById('langEnBtn');
        const langFrBtn = document.getElementById('langFrBtn');

        let currentLang = localStorage.getItem('usersLang') || 'en';

        const translations = {
            en: {
                title: 'Edit User',
                subtitle: 'Update the selected account information.',
                backToUsers: 'Back to Users',
                lastName: 'Last Name',
                firstName: 'First Name',
                dob: 'Date of Birth',
                email: 'Email',
                emailHint: 'Allowed domains: @gmail.com or @esprit.tn',
                newPassword: 'New Password',
                passwordHint: 'The current password is encrypted and cannot be displayed.',
                gender: 'Gender',
                selectGender: 'Select gender',
                female: 'Female',
                male: 'Male',
                role: 'Role',
                selectRole: 'Select role',
                client: 'Client',
                coach: 'Coach',
                nutritionist: 'Nutritionist',
                status: 'Status',
                selectStatus: 'Select status',
                active: 'Active',
                banned: 'Banned',
                deactivated: 'Deactivated',
                pending: 'Pending',
                cancel: 'Cancel',
                updateUser: 'Update User'
            },
            fr: {
                title: 'Modifier utilisateur',
                subtitle: 'Modifiez les informations du compte sélectionné.',
                backToUsers: 'Retour aux utilisateurs',
                lastName: 'Nom',
                firstName: 'Prénom',
                dob: 'Date de naissance',
                email: 'Email',
                emailHint: 'Domaines acceptés : @gmail.com ou @esprit.tn',
                newPassword: 'Nouveau mot de passe',
                passwordHint: 'Le mot de passe actuel est crypté et ne peut pas être affiché.',
                gender: 'Genre',
                selectGender: 'Choisir le genre',
                female: 'Femme',
                male: 'Homme',
                role: 'Rôle',
                selectRole: 'Choisir le rôle',
                client: 'Client',
                coach: 'Coach',
                nutritionist: 'Nutritionniste',
                status: 'Statut',
                selectStatus: 'Choisir le statut',
                active: 'Actif',
                banned: 'Banni',
                deactivated: 'Désactivé',
                pending: 'En attente',
                cancel: 'Annuler',
                updateUser: 'Modifier'
            }
        };

        function applyTheme(theme) {
            const isDark = theme === 'dark';

            document.body.classList.toggle('dark-mode', isDark);
            localStorage.setItem('usersTheme', theme);

            themeToggle.innerHTML = isDark ?
                '<i class="bi bi-sun"></i>' :
                '<i class="bi bi-moon-stars"></i>';
        }

        function applyLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('usersLang', lang);
            document.documentElement.lang = lang;

            langEnBtn.classList.toggle('active', lang === 'en');
            langFrBtn.classList.toggle('active', lang === 'fr');

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.dataset.i18n;

                if (translations[lang] && translations[lang][key]) {
                    el.textContent = translations[lang][key];
                }
            });

            document.querySelectorAll('[data-placeholder-en]').forEach(input => {
                input.placeholder = lang === 'fr' ?
                    input.dataset.placeholderFr :
                    input.dataset.placeholderEn;
            });
        }

        themeToggle.addEventListener('click', function() {
            const newTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            applyTheme(newTheme);
        });

        langEnBtn.addEventListener('click', function() {
            applyLanguage('en');
        });

        langFrBtn.addEventListener('click', function() {
            applyLanguage('fr');
        });

        applyTheme(localStorage.getItem('usersTheme') || 'light');
        applyLanguage(currentLang);
    </script>

</body>

</html>