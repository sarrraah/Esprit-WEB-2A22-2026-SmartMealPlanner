<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UserController.php';
require_once 'admin_auth.php';

$controller = new UserController();

$allUsers = $controller->index();
$users = $allUsers;

if (isset($_GET['filter']) && $_GET['filter'] === 'pending') {
    $users = array_filter($users, function ($u) {
        return strtolower(trim($u['statut'] ?? '')) === 'pending';
    });
}

$search = trim($_GET['search'] ?? '');
$sort = trim($_GET['sort'] ?? '');

if ($search !== '') {
    $users = array_filter($users, function ($u) use ($search) {
        $searchLower = strtolower($search);

        $fullName = strtolower(trim(($u['nom'] ?? '') . ' ' . ($u['prenom'] ?? '')));
        $email = strtolower((string)($u['email'] ?? ''));
        $role = strtolower((string)($u['role'] ?? ''));
        $status = strtolower((string)($u['statut'] ?? ''));
        $gender = strtolower((string)($u['sexe'] ?? ''));
        $id = strtolower((string)($u['id'] ?? ''));

        return strpos($fullName, $searchLower) !== false
            || strpos($email, $searchLower) !== false
            || strpos($role, $searchLower) !== false
            || strpos($status, $searchLower) !== false
            || strpos($gender, $searchLower) !== false
            || strpos($id, $searchLower) !== false;
    });
}

if ($sort !== '') {
    usort($users, function ($a, $b) use ($sort) {
        switch ($sort) {
            case 'name_asc':
                $valueA = strtolower(trim(($a['nom'] ?? '') . ' ' . ($a['prenom'] ?? '')));
                $valueB = strtolower(trim(($b['nom'] ?? '') . ' ' . ($b['prenom'] ?? '')));
                return strcmp($valueA, $valueB);

            case 'name_desc':
                $valueA = strtolower(trim(($a['nom'] ?? '') . ' ' . ($a['prenom'] ?? '')));
                $valueB = strtolower(trim(($b['nom'] ?? '') . ' ' . ($b['prenom'] ?? '')));
                return strcmp($valueB, $valueA);

            case 'id_asc':
                return (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0);

            case 'id_desc':
                return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);

            case 'role_asc':
                $valueA = strtolower(trim((string)($a['role'] ?? '')));
                $valueB = strtolower(trim((string)($b['role'] ?? '')));
                return strcmp($valueA, $valueB);

            case 'status_asc':
                $valueA = strtolower(trim((string)($a['statut'] ?? '')));
                $valueB = strtolower(trim((string)($b['statut'] ?? '')));
                return strcmp($valueA, $valueB);

            default:
                return 0;
        }
    });
}

$totalUsers = count($allUsers);
$tableUsersCount = count($users);

$clientCount = 0;
$coachCount = 0;
$nutritionistCount = 0;
$adminCount = 0;

$activeCount = 0;
$pendingCount = 0;
$bannedCount = 0;
$deactivatedCount = 0;

$maleCount = 0;
$femaleCount = 0;
$otherGenderCount = 0;

foreach ($allUsers as $u) {
    $role = strtolower(trim($u['role'] ?? ''));
    $status = strtolower(trim($u['statut'] ?? ''));
    $gender = strtolower(trim($u['sexe'] ?? ''));

    if ($role === 'client') {
        $clientCount++;
    } elseif ($role === 'coach') {
        $coachCount++;
    } elseif ($role === 'nutritionist') {
        $nutritionistCount++;
    } elseif ($role === 'admin') {
        $adminCount++;
    }

    if ($status === 'active') {
        $activeCount++;
    } elseif ($status === 'pending') {
        $pendingCount++;
    } elseif ($status === 'banned') {
        $bannedCount++;
    } elseif ($status === 'deactivated') {
        $deactivatedCount++;
    }

    if ($gender === 'male') {
        $maleCount++;
    } elseif ($gender === 'female') {
        $femaleCount++;
    } else {
        $otherGenderCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>

    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --red: #dc2626;
            --red-dark: #991b1b;
            --red-soft: #fef2f2;
            --bg: #f8fafc;
            --bg-2: #ffffff;
            --card: rgba(255, 255, 255, 0.88);
            --card-solid: #ffffff;
            --border: rgba(255, 255, 255, 0.75);
            --border-strong: #fee2e2;
            --text: #111827;
            --text-2: #374151;
            --muted: #6b7280;
            --table-head: #fff7f7;
            --table-hover: #fffbfb;
            --input: #ffffff;
            --shadow: 0 20px 55px rgba(15, 23, 42, 0.08);
            --shadow-hover: 0 28px 75px rgba(15, 23, 42, 0.13);
            --scroll-track: #fff1f1;
            --scroll-thumb: #fecaca;
        }

        body.dark-mode {
            --bg: #0f172a;
            --bg-2: #111827;
            --card: rgba(17, 24, 39, 0.86);
            --card-solid: #111827;
            --border: rgba(255, 255, 255, 0.08);
            --border-strong: rgba(248, 113, 113, 0.20);
            --text: #f9fafb;
            --text-2: #e5e7eb;
            --muted: #9ca3af;
            --table-head: rgba(127, 29, 29, 0.24);
            --table-hover: rgba(248, 113, 113, 0.08);
            --input: #0f172a;
            --shadow: 0 24px 70px rgba(0, 0, 0, 0.26);
            --shadow-hover: 0 30px 82px rgba(0, 0, 0, 0.36);
            --scroll-track: rgba(255, 255, 255, 0.08);
            --scroll-thumb: rgba(248, 113, 113, 0.45);
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            padding: 30px;
            color: var(--text-2);
            background:
                radial-gradient(circle at top left, rgba(220, 38, 38, 0.13), transparent 28%),
                radial-gradient(circle at top right, rgba(153, 27, 27, 0.08), transparent 25%),
                linear-gradient(135deg, var(--bg) 0%, var(--bg-2) 100%);
            transition: background 0.3s ease, color 0.3s ease;
        }

        .page-shell {
            max-width: 1420px;
            margin: 0 auto;
            animation: pageFade 0.55s ease both;
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
            gap: 22px;
            margin-bottom: 24px;
        }

        .topbar h1 {
            font-size: 42px;
            line-height: 1.05;
            color: var(--text);
            margin-bottom: 10px;
            letter-spacing: -1.3px;
        }

        .topbar p {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 690px;
        }

        .topbar-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
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

        .top-btn {
            text-decoration: none;
            border: none;
            cursor: pointer;
            padding: 13px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            transition: 0.25s ease;
            white-space: nowrap;
            color: white;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            box-shadow: 0 12px 24px rgba(185, 28, 28, 0.16);
        }

        .top-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(185, 28, 28, 0.22);
            opacity: 0.96;
        }

        .top-btn.mode-active {
            box-shadow:
                0 0 0 4px rgba(220, 38, 38, 0.12),
                0 14px 28px rgba(185, 28, 28, 0.18);
        }

        .pending-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            padding: 0 8px;
            margin-left: 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.22);
            font-size: 12px;
        }

        .analytics-panel {
            margin-bottom: 24px;
            display: grid;
            grid-template-columns: 0.9fr 1.15fr 1.15fr 1.15fr;
            gap: 18px;
        }

        .analytics-card,
        .main-card {
            background: var(--card);
            backdrop-filter: blur(14px);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            transition: 0.28s ease;
        }

        .analytics-card {
            border-radius: 28px;
            padding: 22px;
            position: relative;
            overflow: hidden;
        }

        .analytics-card::after {
            content: "";
            position: absolute;
            right: -35px;
            bottom: -45px;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: rgba(220, 38, 38, 0.065);
            pointer-events: none;
        }

        .analytics-card:hover,
        .main-card:hover {
            box-shadow: var(--shadow-hover);
        }

        .analytics-card:hover {
            transform: translateY(-4px);
        }

        .total-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 310px;
            border: 1px solid var(--border-strong);
        }

        .total-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: #ffffff;
            font-size: 24px;
            box-shadow: 0 16px 28px rgba(185, 28, 28, 0.20);
            margin-bottom: 18px;
        }

        .total-card span {
            display: block;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }

        .total-card strong {
            display: block;
            font-size: 58px;
            line-height: 1;
            letter-spacing: -2px;
            color: var(--text);
            margin-bottom: 12px;
        }

        .total-card p,
        .chart-card p,
        .card-head p {
            color: var(--muted);
            line-height: 1.6;
        }

        .chart-card h3 {
            font-size: 17px;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: -0.2px;
        }

        .chart-card p {
            font-size: 13px;
            margin-bottom: 16px;
        }

        .chart-box {
            height: 230px;
            position: relative;
            z-index: 2;
        }

        .main-card {
            border-radius: 30px;
            padding: 24px;
            animation: cardRise 0.65s ease both;
        }

        @keyframes cardRise {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }

        .card-head h2 {
            font-size: 23px;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: -0.4px;
        }

        .users-count-pill {
            background: rgba(220, 38, 38, 0.08);
            color: #b91c1c;
            font-size: 13px;
            font-weight: 800;
            padding: 11px 15px;
            border-radius: 999px;
            border: 1px solid var(--border-strong);
            white-space: nowrap;
        }

        body.dark-mode .users-count-pill {
            color: #fca5a5;
        }

        .toolbar {
            margin-bottom: 18px;
            padding: 18px;
            background: rgba(254, 242, 242, 0.55);
            border: 1px solid var(--border-strong);
            border-radius: 22px;
        }

        body.dark-mode .toolbar {
            background: rgba(127, 29, 29, 0.12);
        }

        .toolbar-form {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: end;
            width: 100%;
        }

        .toolbar-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 240px;
            flex: 1;
        }

        .toolbar-group label {
            font-size: 13px;
            font-weight: 800;
            color: #7f1d1d;
        }

        body.dark-mode .toolbar-group label {
            color: #fca5a5;
        }

        .toolbar-input,
        .toolbar-select {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid var(--border-strong);
            border-radius: 16px;
            font-size: 14px;
            color: var(--text-2);
            background: var(--input);
            outline: none;
            transition: 0.22s ease;
        }

        .toolbar-input:focus,
        .toolbar-select:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);
            transform: translateY(-1px);
        }

        .table-scroll-area {
            width: 100%;
        }

        .table-wrap {
            overflow-x: auto;
            border-radius: 22px;
            scroll-behavior: smooth;
            border: 1px solid var(--border-strong);
        }

        .table-wrap::-webkit-scrollbar {
            height: 8px;
        }

        .table-wrap::-webkit-scrollbar-track {
            background: var(--scroll-track);
            border-radius: 999px;
        }

        .table-wrap::-webkit-scrollbar-thumb {
            background: var(--scroll-thumb);
            border-radius: 999px;
        }

        .table-wrap::-webkit-scrollbar-thumb:hover {
            background: #dc2626;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1080px;
            background: var(--card-solid);
            overflow: hidden;
            border-radius: 22px;
        }

        thead th {
            background: var(--table-head);
            color: #7f1d1d;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.8px;
            font-weight: 800;
            text-align: left;
            padding: 17px 15px;
            border-bottom: 1px solid var(--border-strong);
        }

        body.dark-mode thead th {
            color: #fca5a5;
        }

        tbody td {
            padding: 16px 15px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            font-size: 14px;
            color: var(--text-2);
            vertical-align: middle;
        }

        tbody tr {
            transition: 0.22s ease;
            cursor: pointer;
        }

        tbody tr:hover {
            background: var(--table-hover);
        }

        tbody tr.pending-row {
            background: rgba(220, 38, 38, 0.08);
            box-shadow: inset 4px 0 0 #dc2626;
        }

        .id-pill {
            display: inline-block;
            padding: 8px 12px;
            background: rgba(148, 163, 184, 0.15);
            border-radius: 999px;
            font-weight: 700;
            color: var(--text);
            min-width: 54px;
            text-align: center;
        }

        .name-cell {
            font-weight: 500;
            color: var(--text-2);
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .email-cell {
            color: var(--text-2);
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
        }

        .badge-role-client {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .badge-role-coach {
            background: #ecfdf3;
            color: #15803d;
        }

        .badge-role-nutritionist {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .badge-role-admin {
            background: #111827;
            color: #ffffff;
        }

        .badge-role-default {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-status-active {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-status-banned {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-status-deactivated {
            background: #e5e7eb;
            color: #4b5563;
        }

        .badge-status-default {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-status-pending {
            background: #fef3c7;
            color: #b45309;
        }

        .status-link {
            text-decoration: none;
        }

        .gender-pill {
            background: #fdf2f8;
            color: #be123c;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            display: inline-block;
            min-width: 78px;
            text-align: center;
        }

        .row-delete-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #dc2626;
            font-size: 19px;
            cursor: pointer;
            padding: 4px;
            transition: 0.22s ease;
        }

        .row-delete-btn:hover {
            color: #991b1b;
            transform: translateY(-50%) scale(1.18);
        }

        .empty-state {
            text-align: center;
            padding: 36px;
            color: var(--muted);
            font-style: italic;
        }

        .table-arrows {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
        }

        .table-arrow-btn {
            width: 46px;
            height: 46px;
            border: 1px solid var(--border-strong);
            background: var(--card-solid);
            color: var(--red);
            border-radius: 999px;
            font-size: 19px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.22s ease;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
        }

        .table-arrow-btn:hover {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 15px 28px rgba(185, 28, 28, 0.18);
        }

        .table-arrow-btn:active {
            transform: translateY(0);
        }

        .scroll-hint {
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
            padding: 0 6px;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.50);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 9999;
        }

        .modal-backdrop.show {
            display: flex;
            animation: modalFade 0.2s ease both;
        }

        @keyframes modalFade {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-card {
            width: 100%;
            max-width: 480px;
            background: var(--card-solid);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
            animation: modalPop 0.25s ease both;
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(8px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-card h3 {
            font-size: 24px;
            color: var(--text);
            margin-bottom: 10px;
        }

        .modal-card p {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.7;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        .modal-btn-secondary {
            border: 1px solid var(--border-strong);
            background: var(--card-solid);
            color: var(--text-2);
            padding: 12px 18px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.22s ease;
        }

        .modal-btn-danger {
            border: none;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(185, 28, 28, 0.16);
            transition: 0.22s ease;
        }

        .modal-btn-secondary:hover,
        .modal-btn-danger:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 1200px) {
            .analytics-panel {
                grid-template-columns: repeat(2, 1fr);
            }

            .total-card {
                min-height: auto;
            }
        }

        @media (max-width: 992px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar h1 {
                font-size: 34px;
            }

            .topbar-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 18px;
            }

            .analytics-panel {
                grid-template-columns: 1fr;
            }

            .card-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-actions {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 4px;
            }

            .toolbar-form {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-group {
                width: 100%;
                min-width: 100%;
            }

            .modal-actions {
                flex-direction: column;
            }

            .modal-btn-secondary,
            .modal-btn-danger {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="page-shell">

        <div class="topbar">
            <div>
                <h1 data-i18n="pageTitle">Users Management</h1>
                <p data-i18n="pageSubtitle">
                    Manage accounts, review professional requests, and monitor user activity through a clean and modern admin workspace.
                </p>
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

                <a href="pending_requests.php" class="top-btn">
                    <span data-i18n="pendingRequests">Pending Requests</span>
                    <span class="pending-count"><?= $pendingCount ?></span>
                </a>

                <a href="add_user.php" class="top-btn" data-i18n="addUser">
                    + Add User
                </a>
            </div>
        </div>

        <div class="analytics-panel">

            <div class="analytics-card total-card">
                <div>
                    <div class="total-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <span data-i18n="totalUsers">Total Users</span>
                    <strong><?= $totalUsers ?></strong>
                </div>

                <p data-i18n="totalUsersDesc">
                    Global count of all registered accounts currently stored in the platform database.
                </p>
            </div>

            <div class="analytics-card chart-card">
                <h3 data-i18n="roleDistribution">Role Distribution</h3>
                <p data-i18n="roleDistributionDesc">Clients, coaches, nutritionists, and admins.</p>
                <div class="chart-box">
                    <canvas id="roleChart"></canvas>
                </div>
            </div>

            <div class="analytics-card chart-card">
                <h3 data-i18n="statusOverview">Status Overview</h3>
                <p data-i18n="statusOverviewDesc">Active, pending, banned, and deactivated users.</p>
                <div class="chart-box">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="analytics-card chart-card">
                <h3 data-i18n="genderDistribution">Gender Distribution</h3>
                <p data-i18n="genderDistributionDesc">Registered user gender repartition.</p>
                <div class="chart-box">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>

        </div>

        <div class="main-card">
            <div class="card-head">
                <div>
                    <h2 data-i18n="userDirectory">User Directory</h2>
                    <p data-i18n="userDirectoryDesc">View all existing accounts, their role, status, and profile details.</p>
                </div>

                <div class="users-count-pill">
                    <?= $tableUsersCount ?> <span data-i18n="usersCount">user(s)</span>
                </div>
            </div>

            <div class="toolbar">
                <div class="toolbar-form">
                    <div class="toolbar-group">
                        <label for="search" data-i18n="search">Search</label>
                        <input
                            type="text"
                            id="search"
                            class="toolbar-input"
                            data-placeholder-en="Search by ID, name, email, role, status, or gender"
                            data-placeholder-fr="Rechercher par ID, nom, email, rôle, statut ou genre"
                            placeholder="Search by ID, name, email, role, status, or gender">
                    </div>

                    <div class="toolbar-group">
                        <label for="sort" data-i18n="sortBy">Sort By</label>
                        <select id="sort" class="toolbar-select">
                            <option value="" data-i18n="defaultOrder">Default Order</option>
                            <option value="id_asc" data-i18n="idLowHigh">ID: Low to High</option>
                            <option value="id_desc" data-i18n="idHighLow">ID: High to Low</option>
                            <option value="name_asc" data-i18n="nameAZ">Name: A to Z</option>
                            <option value="name_desc" data-i18n="nameZA">Name: Z to A</option>
                            <option value="role_asc" data-i18n="roleAZ">Role: A to Z</option>
                            <option value="status_asc" data-i18n="statusAZ">Status: A to Z</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-scroll-area">
                <div class="table-wrap" id="usersTableWrap">
                    <table>
                        <thead>
                            <tr>
                                <th data-i18n="id">ID</th>
                                <th data-i18n="lastName">Last Name</th>
                                <th data-i18n="firstName">First Name</th>
                                <th data-i18n="dob">Date of Birth</th>
                                <th data-i18n="email">Email</th>
                                <th data-i18n="role">Role</th>
                                <th data-i18n="status">Status</th>
                                <th data-i18n="gender">Gender</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $role = strtolower(trim((string)($user['role'] ?? '')));
                                    $status = strtolower(trim((string)($user['statut'] ?? '')));
                                    $genderRaw = trim((string)($user['sexe'] ?? ''));

                                    $roleClass = 'badge-role-default';

                                    if ($role === 'client') {
                                        $roleClass = 'badge-role-client';
                                    } elseif ($role === 'coach') {
                                        $roleClass = 'badge-role-coach';
                                    } elseif ($role === 'nutritionist') {
                                        $roleClass = 'badge-role-nutritionist';
                                    } elseif ($role === 'admin') {
                                        $roleClass = 'badge-role-admin';
                                    }

                                    $statusClass = 'badge-status-default';

                                    if ($status === 'active') {
                                        $statusClass = 'badge-status-active';
                                    } elseif ($status === 'banned') {
                                        $statusClass = 'badge-status-banned';
                                    } elseif ($status === 'deactivated') {
                                        $statusClass = 'badge-status-deactivated';
                                    } elseif ($status === 'pending') {
                                        $statusClass = 'badge-status-pending';
                                    }

                                    $normalizedGender = strtolower(trim($genderRaw));

                                    if ($normalizedGender === 'female') {
                                        $genderDisplay = 'Female';
                                    } elseif ($normalizedGender === 'male') {
                                        $genderDisplay = 'Male';
                                    } else {
                                        $genderDisplay = $genderRaw !== '' ? $genderRaw : '—';
                                    }
                                    ?>

                                    <tr
                                        class="user-row"
                                        data-id="<?= htmlspecialchars((string)$user['id']) ?>"
                                        data-name="<?= htmlspecialchars(strtolower(trim(($user['nom'] ?? '') . ' ' . ($user['prenom'] ?? '')))) ?>"
                                        data-email="<?= htmlspecialchars(strtolower((string)($user['email'] ?? ''))) ?>"
                                        data-role="<?= htmlspecialchars(strtolower((string)($user['role'] ?? ''))) ?>"
                                        data-status="<?= htmlspecialchars(strtolower((string)($user['statut'] ?? ''))) ?>"
                                        data-gender="<?= htmlspecialchars(strtolower($genderDisplay)) ?>">

                                        <td>
                                            <span class="id-pill">
                                                #<?= htmlspecialchars((string)($user['id'] ?? '')) ?>
                                            </span>
                                        </td>

                                        <td class="name-cell" title="<?= htmlspecialchars((string)($user['nom'] ?? '—')) ?>">
                                            <?= htmlspecialchars((string)($user['nom'] ?? '—')) ?>
                                        </td>

                                        <td class="name-cell" title="<?= htmlspecialchars((string)($user['prenom'] ?? '—')) ?>">
                                            <?= htmlspecialchars((string)($user['prenom'] ?? '—')) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars((string)($user['date_naissance'] ?? '—')) ?>
                                        </td>

                                        <td class="email-cell" title="<?= htmlspecialchars((string)($user['email'] ?? '—')) ?>">
                                            <?= htmlspecialchars((string)($user['email'] ?? '—')) ?>
                                        </td>

                                        <td>
                                            <span class="badge <?= $roleClass ?> dynamic-role" data-value="<?= htmlspecialchars($role) ?>">
                                                <?= htmlspecialchars($role !== '' ? ucfirst($role) : '—') ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if ($status === 'pending'): ?>
                                                <a href="review_user_request.php?id=<?= urlencode((string)$user['id']) ?>" class="badge <?= $statusClass ?> status-link dynamic-status" data-value="<?= htmlspecialchars($status) ?>">
                                                    <?= htmlspecialchars(ucfirst($status)) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge <?= $statusClass ?> dynamic-status" data-value="<?= htmlspecialchars($status) ?>">
                                                    <?= htmlspecialchars($status !== '' ? ucfirst($status) : '—') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td style="position: relative; padding-right: 44px;">
                                            <span class="gender-pill dynamic-gender" data-value="<?= htmlspecialchars($normalizedGender) ?>">
                                                <?= htmlspecialchars($genderDisplay) ?>
                                            </span>

                                            <button
                                                type="button"
                                                class="row-delete-btn"
                                                data-id="<?= htmlspecialchars((string)$user['id']) ?>"
                                                data-name="<?= htmlspecialchars((string)($user['nom'] ?? '') . ' ' . (string)($user['prenom'] ?? '')) ?>">
                                                <i class="bi bi-dash-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state" data-i18n="noUsers">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-arrows">
                    <button type="button" class="table-arrow-btn" id="scrollLeftBtn" title="Scroll left">
                        <i class="bi bi-arrow-left"></i>
                    </button>



                    <button type="button" class="table-arrow-btn" id="scrollRightBtn" title="Scroll right">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div id="deleteModal" class="modal-backdrop">
        <div class="modal-card">
            <h3 data-i18n="deleteModalTitle">Delete User</h3>
            <p id="deleteModalText">Are you sure you want to delete this user?</p>

            <form method="POST" action="delete_user.php">
                <input type="hidden" name="id" id="modalDeleteUserId">

                <div class="modal-actions">
                    <button type="button" class="modal-btn-secondary" id="cancelDeleteBtn" data-i18n="no">
                        No
                    </button>

                    <button type="submit" class="modal-btn-danger" data-i18n="yesDelete">
                        Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentLang = localStorage.getItem('usersLang') || 'en';

        const tbody = document.querySelector('tbody');
        const rows = Array.from(document.querySelectorAll('.user-row'));

        const deleteModal = document.getElementById('deleteModal');
        const modalDeleteUserId = document.getElementById('modalDeleteUserId');
        const deleteModalText = document.getElementById('deleteModalText');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

        const searchInput = document.getElementById('search');
        const sortSelect = document.getElementById('sort');

        const usersTableWrap = document.getElementById('usersTableWrap');
        const scrollLeftBtn = document.getElementById('scrollLeftBtn');
        const scrollRightBtn = document.getElementById('scrollRightBtn');

        const themeToggle = document.getElementById('themeToggle');
        const langEnBtn = document.getElementById('langEnBtn');
        const langFrBtn = document.getElementById('langFrBtn');

        const translations = {
            en: {
                pageTitle: 'Users Management',
                pageSubtitle: 'Manage accounts, review professional requests, and monitor user activity through a clean and modern admin workspace.',
                pendingRequests: 'Pending Requests',
                addUser: '+ Add User',
                totalUsers: 'Total Users',
                totalUsersDesc: 'Global count of all registered accounts currently stored in the platform database.',
                roleDistribution: 'Role Distribution',
                roleDistributionDesc: 'Clients, coaches, nutritionists, and admins.',
                statusOverview: 'Status Overview',
                statusOverviewDesc: 'Active, pending, banned, and deactivated users.',
                genderDistribution: 'Gender Distribution',
                genderDistributionDesc: 'Registered user gender repartition.',
                userDirectory: 'User Directory',
                userDirectoryDesc: 'View all existing accounts, their role, status, and profile details.',
                usersCount: 'user(s)',
                search: 'Search',
                sortBy: 'Sort By',
                defaultOrder: 'Default Order',
                idLowHigh: 'ID: Low to High',
                idHighLow: 'ID: High to Low',
                nameAZ: 'Name: A to Z',
                nameZA: 'Name: Z to A',
                roleAZ: 'Role: A to Z',
                statusAZ: 'Status: A to Z',
                id: 'ID',
                lastName: 'Last Name',
                firstName: 'First Name',
                dob: 'Date of Birth',
                email: 'Email',
                role: 'Role',
                status: 'Status',
                gender: 'Gender',
                noUsers: 'No users found.',
                deleteModalTitle: 'Delete User',
                deleteConfirm: 'Are you sure you want to delete',
                deleteConfirmDefault: 'Are you sure you want to delete this user?',
                no: 'No',
                yesDelete: 'Yes, Delete',
                clients: 'Clients',
                coaches: 'Coaches',
                nutritionists: 'Nutritionists',
                admins: 'Admins',
                active: 'Active',
                pending: 'Pending',
                banned: 'Banned',
                deactivated: 'Deactivated',
                male: 'Male',
                female: 'Female',
                other: 'Other',
                client: 'Client',
                coach: 'Coach',
                nutritionist: 'Nutritionist',
                admin: 'Admin'
            },
            fr: {
                pageTitle: 'Gestion des Utilisateurs',
                pageSubtitle: 'Gérez les comptes, vérifiez les demandes professionnelles et suivez l’activité des utilisateurs dans un espace admin moderne.',
                pendingRequests: 'Demandes en attente',
                addUser: '+ Ajouter',
                totalUsers: 'Total Utilisateurs',
                totalUsersDesc: 'Nombre global de tous les comptes enregistrés actuellement dans la base de données.',
                roleDistribution: 'Répartition des rôles',
                roleDistributionDesc: 'Clients, coachs, nutritionnistes et admins.',
                statusOverview: 'Aperçu des statuts',
                statusOverviewDesc: 'Utilisateurs actifs, en attente, bannis et désactivés.',
                genderDistribution: 'Répartition du genre',
                genderDistributionDesc: 'Répartition des utilisateurs inscrits selon le genre.',
                userDirectory: 'Liste des utilisateurs',
                userDirectoryDesc: 'Consultez les comptes, leurs rôles, statuts et informations principales.',
                usersCount: 'utilisateur(s)',
                search: 'Recherche',
                sortBy: 'Trier par',
                defaultOrder: 'Ordre par défaut',
                idLowHigh: 'ID : croissant',
                idHighLow: 'ID : décroissant',
                nameAZ: 'Nom : A à Z',
                nameZA: 'Nom : Z à A',
                roleAZ: 'Rôle : A à Z',
                statusAZ: 'Statut : A à Z',
                id: 'ID',
                lastName: 'Nom',
                firstName: 'Prénom',
                dob: 'Date de naissance',
                email: 'Email',
                role: 'Rôle',
                status: 'Statut',
                gender: 'Genre',
                noUsers: 'Aucun utilisateur trouvé.',
                deleteModalTitle: 'Supprimer utilisateur',
                deleteConfirm: 'Voulez-vous vraiment supprimer',
                deleteConfirmDefault: 'Voulez-vous vraiment supprimer cet utilisateur ?',
                no: 'Non',
                yesDelete: 'Oui, supprimer',
                clients: 'Clients',
                coaches: 'Coachs',
                nutritionists: 'Nutritionnistes',
                admins: 'Admins',
                active: 'Actif',
                pending: 'En attente',
                banned: 'Banni',
                deactivated: 'Désactivé',
                male: 'Homme',
                female: 'Femme',
                other: 'Autre',
                client: 'Client',
                coach: 'Coach',
                nutritionist: 'Nutritionniste',
                admin: 'Admin'
            }
        };



        function openDeleteModal(userId, userName) {
            modalDeleteUserId.value = userId;

            if (currentLang === 'fr') {
                deleteModalText.textContent = translations.fr.deleteConfirm + ' ' + userName + ' ?';
            } else {
                deleteModalText.textContent = translations.en.deleteConfirm + ' ' + userName + '?';
            }

            deleteModal.classList.add('show');
        }

        function closeDeleteModal() {
            deleteModal.classList.remove('show');
        }

        function getFilteredRows() {
            const searchValue = searchInput.value.trim().toLowerCase();
            const sortValue = sortSelect.value;

            let filteredRows = rows.filter(row => {
                const combined = (
                    (row.dataset.id || '') + ' ' +
                    (row.dataset.name || '') + ' ' +
                    (row.dataset.email || '') + ' ' +
                    (row.dataset.role || '') + ' ' +
                    (row.dataset.status || '') + ' ' +
                    (row.dataset.gender || '')
                ).toLowerCase();

                return combined.includes(searchValue);
            });

            filteredRows.sort((a, b) => {
                if (sortValue === 'id_asc') return Number(a.dataset.id) - Number(b.dataset.id);
                if (sortValue === 'id_desc') return Number(b.dataset.id) - Number(a.dataset.id);
                if (sortValue === 'name_asc') return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                if (sortValue === 'name_desc') return (b.dataset.name || '').localeCompare(a.dataset.name || '');
                if (sortValue === 'role_asc') return (a.dataset.role || '').localeCompare(b.dataset.role || '');
                if (sortValue === 'status_asc') return (a.dataset.status || '').localeCompare(b.dataset.status || '');

                return 0;
            });

            return filteredRows;
        }

        function renderRows(rowsToShow) {
            tbody.innerHTML = '';

            if (rowsToShow.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="empty-state" data-i18n="noUsers">${translations[currentLang].noUsers}</td>
                    </tr>
                `;
                return;
            }

            rowsToShow.forEach(row => {
                row.style.display = '';
                tbody.appendChild(row);
            });

            translateDynamicBadges();
        }

        function applyFilters() {
            renderRows(getFilteredRows());
        }

        function applyLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('usersLang', lang);

            document.documentElement.lang = lang;

            langEnBtn.classList.toggle('active', lang === 'en');
            langFrBtn.classList.toggle('active', lang === 'fr');

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.dataset.i18n;

                if (translations[lang][key]) {
                    el.textContent = translations[lang][key];
                }
            });

            if (searchInput) {
                searchInput.placeholder = lang === 'fr' ?
                    searchInput.dataset.placeholderFr :
                    searchInput.dataset.placeholderEn;
            }

            translateDynamicBadges();
            updateChartsLanguage();
        }

        function translateDynamicBadges() {
            document.querySelectorAll('.dynamic-role').forEach(el => {
                const value = (el.dataset.value || '').toLowerCase();
                el.textContent = translations[currentLang][value] || '—';
            });

            document.querySelectorAll('.dynamic-status').forEach(el => {
                const value = (el.dataset.value || '').toLowerCase();
                el.textContent = translations[currentLang][value] || '—';
            });

            document.querySelectorAll('.dynamic-gender').forEach(el => {
                const value = (el.dataset.value || '').toLowerCase();
                el.textContent = translations[currentLang][value] || el.textContent;
            });
        }

        function applyTheme(theme) {
            const isDark = theme === 'dark';

            document.body.classList.toggle('dark-mode', isDark);
            localStorage.setItem('usersTheme', theme);

            themeToggle.innerHTML = isDark ?
                '<i class="bi bi-sun"></i>' :
                '<i class="bi bi-moon-stars"></i>';

            updateChartTheme();
        }

        searchInput.addEventListener('input', applyFilters);
        sortSelect.addEventListener('change', applyFilters);






        cancelDeleteBtn.addEventListener('click', function() {
            closeDeleteModal();
        });

        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });

        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.row-delete-btn');

            if (!deleteBtn) {
                return;
            }

            e.stopPropagation();

            const userId = deleteBtn.dataset.id;
            const userName = deleteBtn.dataset.name || 'this user';

            openDeleteModal(userId, userName);
        });

        if (usersTableWrap && scrollLeftBtn && scrollRightBtn) {
            scrollLeftBtn.addEventListener('click', function() {
                usersTableWrap.scrollBy({
                    left: -420,
                    behavior: 'smooth'
                });
            });

            scrollRightBtn.addEventListener('click', function() {
                usersTableWrap.scrollBy({
                    left: 420,
                    behavior: 'smooth'
                });
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

        const chartColors = [
            '#dc2626',
            '#f97316',
            '#7c3aed',
            '#2563eb',
            '#16a34a',
            '#6b7280'
        ];

        const roleChart = new Chart(document.getElementById('roleChart'), {
            type: 'doughnut',
            data: {
                labels: [
                    translations[currentLang].clients,
                    translations[currentLang].coaches,
                    translations[currentLang].nutritionists,
                    translations[currentLang].admins
                ],
                datasets: [{
                    data: [
                        <?= $clientCount ?>,
                        <?= $coachCount ?>,
                        <?= $nutritionistCount ?>,
                        <?= $adminCount ?>
                    ],
                    backgroundColor: chartColors,
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '66%',
                animation: {
                    duration: 1100,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 14,
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });

        const statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: [
                    translations[currentLang].active,
                    translations[currentLang].pending,
                    translations[currentLang].banned,
                    translations[currentLang].deactivated
                ],
                datasets: [{
                    data: [
                        <?= $activeCount ?>,
                        <?= $pendingCount ?>,
                        <?= $bannedCount ?>,
                        <?= $deactivatedCount ?>
                    ],
                    backgroundColor: [
                        '#16a34a',
                        '#f59e0b',
                        '#dc2626',
                        '#6b7280'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1100,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 14,
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                }
            }
        });

        const genderChart = new Chart(document.getElementById('genderChart'), {
            type: 'bar',
            data: {
                labels: [
                    translations[currentLang].male,
                    translations[currentLang].female,
                    translations[currentLang].other
                ],
                datasets: [{
                    label: 'Users',
                    data: [
                        <?= $maleCount ?>,
                        <?= $femaleCount ?>,
                        <?= $otherGenderCount ?>
                    ],
                    backgroundColor: [
                        '#2563eb',
                        '#be123c',
                        '#6b7280'
                    ],
                    borderRadius: 14,
                    barThickness: 38
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1100,
                    easing: 'easeOutQuart'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(107, 114, 128, 0.12)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        function updateChartsLanguage() {
            roleChart.data.labels = [
                translations[currentLang].clients,
                translations[currentLang].coaches,
                translations[currentLang].nutritionists,
                translations[currentLang].admins
            ];

            statusChart.data.labels = [
                translations[currentLang].active,
                translations[currentLang].pending,
                translations[currentLang].banned,
                translations[currentLang].deactivated
            ];

            genderChart.data.labels = [
                translations[currentLang].male,
                translations[currentLang].female,
                translations[currentLang].other
            ];

            roleChart.update();
            statusChart.update();
            genderChart.update();
        }

        function updateChartTheme() {
            const isDark = document.body.classList.contains('dark-mode');
            const textColor = isDark ? '#e5e7eb' : '#374151';
            const gridColor = isDark ? 'rgba(229, 231, 235, 0.12)' : 'rgba(107, 114, 128, 0.12)';
            const borderColor = isDark ? '#111827' : '#ffffff';

            [roleChart, statusChart].forEach(chart => {
                chart.data.datasets[0].borderColor = borderColor;
                chart.options.plugins.legend.labels.color = textColor;
                chart.update();
            });

            genderChart.options.scales.x.ticks.color = textColor;
            genderChart.options.scales.y.ticks.color = textColor;
            genderChart.options.scales.y.grid.color = gridColor;
            genderChart.update();
        }

        renderRows(rows);
        applyLanguage(currentLang);
        applyTheme(localStorage.getItem('usersTheme') || 'light');
        const userRows = document.querySelectorAll('.user-row');

        userRows.forEach(row => {
            row.addEventListener('dblclick', function(e) {
                if (e.target.closest('.status-link')) {
                    return;
                }

                if (e.target.closest('.row-delete-btn')) {
                    return;
                }

                const userId = this.dataset.id;

                if (userId) {
                    window.location.href = 'edit_user.php?id=' + encodeURIComponent(userId);
                }
            });
        });
    </script>

</body>

</html>