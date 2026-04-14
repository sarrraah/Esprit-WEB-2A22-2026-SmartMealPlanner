<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UserController.php';

$controller = new UserController();
$users = $controller->index();

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

$totalUsers = count($users);
$clientCount = 0;
$coachCount = 0;
$nutritionistCount = 0;
$pendingCount = 0;

foreach ($users as $u) {
    $role = strtolower(trim($u['role'] ?? ''));
    $status = strtolower(trim($u['statut'] ?? ''));

    if ($role === 'client') {
        $clientCount++;
    } elseif ($role === 'coach') {
        $coachCount++;
    } elseif ($role === 'nutritionist') {
        $nutritionistCount++;
    }

    if ($status === 'pending') {
        $pendingCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
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
            max-width: 1380px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 22px;
        }

        .topbar h1 {
            font-size: 40px;
            line-height: 1.05;
            color: #111827;
            margin-bottom: 8px;
        }

        .topbar p {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
            max-width: 680px;
        }

        .topbar-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .top-btn {
            text-decoration: none;
            border: none;
            cursor: pointer;
            padding: 14px 20px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.25s ease;
            white-space: nowrap;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            box-shadow: 0 14px 28px rgba(185, 28, 28, 0.16);
        }

        .top-btn:hover {
            transform: translateY(-2px);
            opacity: 0.96;
        }

        .top-btn.mode-active {
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12), 0 14px 28px rgba(185, 28, 28, 0.16);
        }

        .summary-card {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: 24px;
            padding: 22px;
            box-shadow: 0 16px 38px rgba(15, 23, 42, 0.06);
            margin-bottom: 22px;
        }

        .summary-title {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 16px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
        }

        .summary-box {
            background: #fffafa;
            border: 1px solid #fee2e2;
            border-radius: 18px;
            padding: 18px;
        }

        .summary-box span {
            display: block;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .summary-box strong {
            font-size: 28px;
            color: #b91c1c;
        }

        .summary-box.pending-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
        }

        .summary-box.pending-box strong {
            color: #b45309;
        }

        .main-card {
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 28px;
            padding: 22px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.07);
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }

        .card-head h2 {
            font-size: 22px;
            color: #111827;
            margin-bottom: 6px;
        }

        .card-head p {
            font-size: 14px;
            color: #6b7280;
        }

        .users-count-pill {
            background: #fef2f2;
            color: #b91c1c;
            font-size: 13px;
            font-weight: bold;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid #fecaca;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            padding: 18px;
            background: rgba(255, 250, 250, 0.95);
            border: 1px solid #fee2e2;
            border-radius: 20px;
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
            min-width: 220px;
            flex: 1;
        }

        .toolbar-group label {
            font-size: 13px;
            font-weight: bold;
            color: #7f1d1d;
        }

        .toolbar-input,
        .toolbar-select {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #fecaca;
            border-radius: 14px;
            font-size: 14px;
            color: #374151;
            background: #ffffff;
            outline: none;
            transition: 0.2s ease;
        }

        .toolbar-input:focus,
        .toolbar-select:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.10);
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: end;
        }

        .toolbar-btn {
            text-decoration: none;
            border: none;
            cursor: pointer;
            padding: 13px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.25s ease;
            white-space: nowrap;
        }

        .toolbar-btn-primary {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #ffffff;
            box-shadow: 0 14px 28px rgba(185, 28, 28, 0.16);
        }

        .toolbar-btn-secondary {
            background: #ffffff;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .toolbar-btn:hover {
            transform: translateY(-2px);
            opacity: 0.96;
        }

        .table-wrap {
            overflow-x: auto;
            border-radius: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1080px;
            background: white;
            overflow: hidden;
            border-radius: 20px;
        }

        thead th {
            background: #fff7f7;
            color: #7f1d1d;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.8px;
            font-weight: bold;
            text-align: left;
            padding: 16px 15px;
            border-bottom: 1px solid #fee2e2;
        }

        tbody td {
            padding: 16px 15px;
            border-bottom: 1px solid #f1f3f7;
            font-size: 14px;
            color: #374151;
            vertical-align: middle;
        }

        tbody tr {
            transition: 0.2s ease;
            cursor: pointer;
        }

        tbody tr:hover {
            background: #fffcfc;
        }

        tbody tr.pending-row {
            background: #fff5f5;
            box-shadow: inset 4px 0 0 #dc2626;
        }

        .id-pill {
            display: inline-block;
            padding: 8px 12px;
            background: #f3f4f6;
            border-radius: 999px;
            font-weight: bold;
            color: #111827;
            min-width: 54px;
            text-align: center;
        }

        .name-cell {
            font-weight: bold;
            color: #111827;
        }

        .email-cell {
            color: #4b5563;
        }

        .badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
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
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #fef3c7;
            color: #b45309;
            font-weight: 600;
        }

        .badge-status-pending:hover {
            opacity: 0.9;
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
            font-weight: bold;
            display: inline-block;
            min-width: 78px;
            text-align: center;
        }

        .empty-state {
            text-align: center;
            padding: 36px;
            color: #9ca3af;
            font-style: italic;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.38);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 9999;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-card {
            width: 100%;
            max-width: 480px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 26px;
            padding: 26px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.16);
        }

        .modal-card h3 {
            font-size: 24px;
            color: #111827;
            margin-bottom: 10px;
        }

        .modal-card p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.7;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 22px;
        }

        .modal-btn-secondary {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #374151;
            padding: 12px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-btn-danger {
            border: none;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(185, 28, 28, 0.16);
        }

        @media (max-width: 992px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar h1 {
                font-size: 32px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 18px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .card-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-actions {
                width: 100%;
            }

            .top-btn {
                width: 100%;
                text-align: center;
            }

            .toolbar {
                padding: 16px;
            }

            .toolbar-form {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-group {
                width: 100%;
                min-width: 100%;
            }

            .toolbar-actions {
                width: 100%;
                flex-direction: column;
            }

            .toolbar-btn {
                width: 100%;
                text-align: center;
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
                <h1>Users Management</h1>
                <p>Manage all registered platform users from one organized and professional admin space.</p>
            </div>

            <div class="topbar-actions">
                <a href="add_user.php" class="top-btn">+ Add User</a>
                <button type="button" id="editModeBtn" class="top-btn">Edit User</button>
                <button type="button" id="deleteModeBtn" class="top-btn">Delete User</button>
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-title">Platform Overview</div>
            <div class="summary-grid">
                <div class="summary-box">
                    <span>Total Users</span>
                    <strong><?= $totalUsers ?></strong>
                </div>
                <div class="summary-box">
                    <span>Clients</span>
                    <strong><?= $clientCount ?></strong>
                </div>
                <div class="summary-box">
                    <span>Coaches</span>
                    <strong><?= $coachCount ?></strong>
                </div>
                <div class="summary-box">
                    <span>Nutritionists</span>
                    <strong><?= $nutritionistCount ?></strong>
                </div>
                <a href="pending_requests.php" class="summary-box" style="text-decoration:none;">
                    <span>Pending Requests</span>
                    <strong><?= $pendingCount ?></strong>
                </a>
            </div>
        </div>

        <div class="main-card">
            <div class="card-head">
                <div>
                    <h2>User Directory</h2>
                    <p>View all existing accounts, their role, status, and profile details.</p>
                </div>
                <div class="users-count-pill"><?= $totalUsers ?> user(s)</div>
            </div>

            <div class="toolbar">
                <div class="toolbar-form">
                    <div class="toolbar-group">
                        <label for="search">Search</label>
                        <input
                            type="text"
                            id="search"
                            class="toolbar-input"
                            placeholder="Search by ID, name, email, role, status, or gender">
                    </div>

                    <div class="toolbar-group">
                        <label for="sort">Sort By</label>
                        <select id="sort" class="toolbar-select">
                            <option value="">Default Order</option>
                            <option value="id_asc">ID: Low to High</option>
                            <option value="id_desc">ID: High to Low</option>
                            <option value="name_asc">Name: A to Z</option>
                            <option value="name_desc">Name: Z to A</option>
                            <option value="role_asc">Role: A to Z</option>
                            <option value="status_asc">Status: A to Z</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Date of Birth</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Gender</th>
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
                                    <td><span class="id-pill">#<?= htmlspecialchars((string)($user['id'] ?? '')) ?></span></td>
                                    <td class="name-cell"><?= htmlspecialchars((string)($user['nom'] ?? '—')) ?></td>
                                    <td class="name-cell"><?= htmlspecialchars((string)($user['prenom'] ?? '—')) ?></td>
                                    <td><?= htmlspecialchars((string)($user['date_naissance'] ?? '—')) ?></td>
                                    <td class="email-cell"><?= htmlspecialchars((string)($user['email'] ?? '—')) ?></td>
                                    <td>
                                        <span class="badge <?= $roleClass ?>">
                                            <?= htmlspecialchars($role !== '' ? ucfirst($role) : '—') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($status === 'pending'): ?>
                                            <a href="review_user_request.php?id=<?= urlencode((string)$user['id']) ?>" class="badge <?= $statusClass ?> status-link">
                                                <?= htmlspecialchars(ucfirst($status)) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge <?= $statusClass ?>">
                                                <?= htmlspecialchars($status !== '' ? ucfirst($status) : '—') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="gender-pill"><?= htmlspecialchars($genderDisplay) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="empty-state">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div id="deleteModal" class="modal-backdrop">
        <div class="modal-card">
            <h3>Delete User</h3>
            <p id="deleteModalText">Are you sure you want to delete this user?</p>

            <form method="POST" action="delete_user.php">
                <input type="text" name="id" id="modalDeleteUserId" style="display:none;">
                <div class="modal-actions">
                    <button type="button" class="modal-btn-secondary" id="cancelDeleteBtn">No</button>
                    <button type="submit" class="modal-btn-danger">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentMode = null;

        const tbody = document.querySelector('tbody');
        const rows = Array.from(document.querySelectorAll('.user-row'));

        const editModeBtn = document.getElementById('editModeBtn');
        const deleteModeBtn = document.getElementById('deleteModeBtn');
        const deleteModal = document.getElementById('deleteModal');
        const modalDeleteUserId = document.getElementById('modalDeleteUserId');
        const deleteModalText = document.getElementById('deleteModalText');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

        const searchInput = document.getElementById('search');
        const sortSelect = document.getElementById('sort');

        function clearRowState() {
            rows.forEach(row => row.classList.remove('pending-row'));
        }

        function setMode(mode) {
            currentMode = mode;
            editModeBtn.classList.remove('mode-active');
            deleteModeBtn.classList.remove('mode-active');

            if (mode === 'edit') {
                editModeBtn.classList.add('mode-active');
            } else if (mode === 'delete') {
                deleteModeBtn.classList.add('mode-active');
            }
        }

        function openDeleteModal(userId, userName) {
            modalDeleteUserId.value = userId;
            deleteModalText.textContent = 'Are you sure you want to delete ' + userName + '?';
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
                    <td colspan="8" class="empty-state">No users found.</td>
                </tr>
            `;
                return;
            }

            rowsToShow.forEach(row => {
                row.style.display = '';
                tbody.appendChild(row);
            });
        }

        function applyFilters() {
            renderRows(getFilteredRows());
        }

        // LIVE filtering
        searchInput.addEventListener('input', applyFilters);
        sortSelect.addEventListener('change', applyFilters);

        // Edit / Delete mode buttons
        editModeBtn.addEventListener('click', function() {
            setMode('edit');
            clearRowState();
        });

        deleteModeBtn.addEventListener('click', function() {
            setMode('delete');
            clearRowState();
        });

        // Row click logic
        rows.forEach(row => {
            row.addEventListener('click', function(e) {
                const userId = this.dataset.id;
                const userName = this.dataset.name || 'this user';

                if (e.target.closest('.status-link')) {
                    return;
                }

                clearRowState();
                this.classList.add('pending-row');

                if (currentMode === 'edit') {
                    window.location.href = 'edit_user.php?id=' + encodeURIComponent(userId);
                    return;
                }

                if (currentMode === 'delete') {
                    openDeleteModal(userId, userName);
                }
            });

            row.addEventListener('dblclick', function(e) {
                if (e.target.closest('.status-link')) {
                    return;
                }

                const userId = this.dataset.id;
                window.location.href = 'edit_user.php?id=' + encodeURIComponent(userId);
            });
        });

        // Modal handling
        cancelDeleteBtn.addEventListener('click', function() {
            closeDeleteModal();
        });

        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });

        // Initial render (IMPORTANT)
        renderRows(rows);
    </script>

</body>

</html>