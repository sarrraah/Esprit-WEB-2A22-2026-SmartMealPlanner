<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UserController.php';

$controller = new UserController();
$users = $controller->index();

$totalUsers = count($users);
$clientCount = 0;
$coachCount = 0;
$nutritionistCount = 0;

foreach ($users as $u) {
    $role = strtolower(trim($u['role'] ?? ''));

    if ($role === 'client') {
        $clientCount++;
    } elseif ($role === 'coach') {
        $coachCount++;
    } elseif ($role === 'nutritionist') {
        $nutritionistCount++;
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
            grid-template-columns: repeat(4, 1fr);
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

        .badge-status-default {
            background: #f3f4f6;
            color: #374151;
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
                                    data-name="<?= htmlspecialchars(trim(($user['nom'] ?? '') . ' ' . ($user['prenom'] ?? ''))) ?>">
                                    <td><span class="id-pill">#<?= htmlspecialchars((string)($user['id'] ?? '')) ?></span></td>
                                    <td class="name-cell"><?= htmlspecialchars((string)($user['nom'] ?? '—')) ?></td>
                                    <td class="name-cell"><?= htmlspecialchars((string)($user['prenom'] ?? '—')) ?></td>
                                    <td><?= htmlspecialchars((string)($user['date_naissance'] ?? '—')) ?></td>
                                    <td class="email-cell"><?= htmlspecialchars((string)($user['email'] ?? '—')) ?></td>
                                    <td>
                                        <span class="badge <?= $roleClass ?>">
                                            <?= htmlspecialchars($role !== '' ? $role : '—') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($status !== '' ? $status : '—') ?>
                                        </span>
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

        const rows = document.querySelectorAll('.user-row');
        const editModeBtn = document.getElementById('editModeBtn');
        const deleteModeBtn = document.getElementById('deleteModeBtn');
        const deleteModal = document.getElementById('deleteModal');
        const modalDeleteUserId = document.getElementById('modalDeleteUserId');
        const deleteModalText = document.getElementById('deleteModalText');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

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

        editModeBtn.addEventListener('click', function() {
            setMode('edit');
            clearRowState();
        });

        deleteModeBtn.addEventListener('click', function() {
            setMode('delete');
            clearRowState();
        });

        rows.forEach(row => {
            row.addEventListener('click', function() {
                const userId = this.dataset.id;
                const userName = this.dataset.name || 'this user';

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

            row.addEventListener('dblclick', function() {
                const userId = this.dataset.id;
                window.location.href = 'edit_user.php?id=' + encodeURIComponent(userId);
            });
        });

        cancelDeleteBtn.addEventListener('click', function() {
            closeDeleteModal();
        });

        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });
    </script>

</body>

</html>