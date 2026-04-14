<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/UserController.php';

$controller = new UserController();
$users = $controller->index();

$pendingUsers = array_values(array_filter($users, function ($u) {
    return isset($u['statut']) && strtolower(trim((string)$u['statut'])) === 'pending';
}));

$pendingCount = count($pendingUsers);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Requests</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #fafbff 0%, #f8fafc 50%, #ffffff 100%);
            color: #1f2937;
            padding: 30px;
        }

        .page-shell {
            max-width: 1120px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 24px;
        }

        .topbar h1 {
            font-size: 34px;
            line-height: 1.1;
            color: #111827;
            margin-bottom: 8px;
        }

        .topbar p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.7;
            max-width: 720px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #ffffff;
            color: #b91c1c;
            padding: 11px 15px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid #e5e7eb;
            transition: 0.2s ease;
            white-space: nowrap;
        }

        .back-btn:hover {
            background: #fff7f7;
            border-color: #fecaca;
        }

        .overview-card {
            background: #ffffff;
            border: 1px solid #eceff3;
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }

        .overview-title {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 14px;
        }

        .overview-box {
            background: #fffafa;
            border: 1px solid #fee2e2;
            border-radius: 16px;
            padding: 16px;
            max-width: 240px;
        }

        .overview-box span {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .overview-box strong {
            font-size: 24px;
            color: #b91c1c;
        }

        .requests-card {
            background: #ffffff;
            border: 1px solid #eceff3;
            border-radius: 22px;
            padding: 20px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }

        .requests-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }

        .requests-head h2 {
            font-size: 22px;
            color: #111827;
            margin-bottom: 6px;
        }

        .requests-head p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
        }

        .count-pill {
            background: #fef2f2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid #fecaca;
            white-space: nowrap;
        }

        .toolbar {
            display: grid;
            grid-template-columns: 1.4fr 0.8fr;
            gap: 12px;
            margin-bottom: 18px;
        }

        .search-input,
        .filter-select {
            width: 100%;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: 0.2s ease;
        }

        .search-input:focus,
        .filter-select:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.08);
        }

        .requests-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .request-item {
            background: #ffffff;
            border: 1px solid #edf1f5;
            border-radius: 18px;
            padding: 18px;
            text-decoration: none;
            color: inherit;
            transition: 0.22s ease;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.03);
            display: block;
            opacity: 0;
            transform: translateY(10px);
            animation: cardAppear 0.45s ease forwards;
        }

        .request-item:hover {
            border-color: #fecaca;
            background: #fffafa;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        }

        @keyframes cardAppear {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .request-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .request-name {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .request-email {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.6;
            word-break: break-word;
        }

        .role-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
            flex-shrink: 0;
        }

        .role-badge.coach {
            background: #ecfdf3;
            color: #15803d;
        }

        .role-badge.nutritionist {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .role-badge.default {
            background: #f3f4f6;
            color: #374151;
        }

        .detail-row {
            margin-top: 12px;
        }

        .detail-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 13px;
            color: #374151;
            line-height: 1.7;
            word-break: break-word;
        }

        .open-line {
            margin-top: 16px;
            font-size: 13px;
            font-weight: 700;
            color: #b91c1c;
        }

        .empty-state {
            text-align: center;
            padding: 34px 20px;
            color: #9ca3af;
            font-style: italic;
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #edf1f5;
        }

        .hidden-card {
            display: none;
        }

        @media (max-width: 900px) {
            .requests-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar h1 {
                font-size: 30px;
            }

            .toolbar {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 18px;
            }

            .requests-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .request-top {
                flex-direction: column;
            }

            .overview-box {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="page-shell">

        <div class="topbar">
            <div>
                <h1>Pending Requests</h1>
                <p>Review all professional account requests from coaches and nutritionists in one clean, organized admin space.</p>
            </div>

            <a href="users.php" class="back-btn">← Back to Users</a>
        </div>

        <div class="overview-card">
            <div class="overview-title">Request Overview</div>
            <div class="overview-box">
                <span>Total Pending Requests</span>
                <strong id="pendingCountLabel"><?= $pendingCount ?></strong>
            </div>
        </div>

        <div class="requests-card">
            <div class="requests-head">
                <div>
                    <h2>Requests Waiting for Review</h2>
                    <p>Use search or filter to quickly find the request you want to review.</p>
                </div>
                <div class="count-pill"><span id="visibleCount"><?= $pendingCount ?></span> request(s)</div>
            </div>

            <div class="toolbar">
                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="Search by name or email">

                <select id="roleFilter" class="filter-select">
                    <option value="all">All roles</option>
                    <option value="coach">Coach</option>
                    <option value="nutritionist">Nutritionist</option>
                </select>
            </div>

            <?php if (!empty($pendingUsers)): ?>
                <div class="requests-grid" id="requestsGrid">
                    <?php foreach ($pendingUsers as $index => $user): ?>
                        <?php
                        $role = strtolower(trim((string)($user['role'] ?? '')));
                        $roleClass = 'default';
                        if ($role === 'coach') {
                            $roleClass = 'coach';
                        } elseif ($role === 'nutritionist') {
                            $roleClass = 'nutritionist';
                        }

                        $fullName = trim((string)(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')));
                        $email = (string)($user['email'] ?? '');
                        $experience = (string)($user['experience'] ?? '—');
                        $speciality = (string)($user['speciality'] ?? '—');
                        ?>
                        <a
                            href="review_user_request.php?id=<?= urlencode((string)$user['id']) ?>"
                            class="request-item"
                            data-name="<?= htmlspecialchars(strtolower($fullName)) ?>"
                            data-email="<?= htmlspecialchars(strtolower($email)) ?>"
                            data-role="<?= htmlspecialchars($role) ?>"
                            style="animation-delay: <?= 0.04 * $index ?>s;">
                            <div class="request-top">
                                <div>
                                    <div class="request-name">
                                        <?= htmlspecialchars($fullName !== '' ? $fullName : 'Unnamed User') ?>
                                    </div>
                                    <div class="request-email">
                                        <?= htmlspecialchars($email) ?>
                                    </div>
                                </div>

                                <span class="role-badge <?= htmlspecialchars($roleClass) ?>">
                                    <?= htmlspecialchars($role !== '' ? $role : 'request') ?>
                                </span>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Years of Experience</span>
                                <div class="detail-value">
                                    <?= htmlspecialchars($experience) ?>
                                </div>
                            </div>

                            <div class="detail-row">
                                <span class="detail-label">Speciality</span>
                                <div class="detail-value">
                                    <?= htmlspecialchars($speciality) ?>
                                </div>
                            </div>

                            <div class="open-line">Review Request →</div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="empty-state hidden-card" id="noResultsState">
                    No requests match your search or filter.
                </div>
            <?php else: ?>
                <div class="empty-state">
                    There are no pending requests at the moment.
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const cards = document.querySelectorAll('.request-item');
        const visibleCount = document.getElementById('visibleCount');
        const noResultsState = document.getElementById('noResultsState');

        function filterRequests() {
            const searchValue = (searchInput?.value || '').trim().toLowerCase();
            const roleValue = roleFilter?.value || 'all';

            let shown = 0;

            cards.forEach(card => {
                const name = card.dataset.name || '';
                const email = card.dataset.email || '';
                const role = card.dataset.role || '';

                const matchesSearch =
                    name.includes(searchValue) ||
                    email.includes(searchValue);

                const matchesRole =
                    roleValue === 'all' || role === roleValue;

                if (matchesSearch && matchesRole) {
                    card.classList.remove('hidden-card');
                    shown++;
                } else {
                    card.classList.add('hidden-card');
                }
            });

            if (visibleCount) {
                visibleCount.textContent = shown;
            }

            if (noResultsState) {
                noResultsState.classList.toggle('hidden-card', shown !== 0);
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterRequests);
        }

        if (roleFilter) {
            roleFilter.addEventListener('change', filterRequests);
        }
    </script>

</body>

</html>