<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';

$pdo = config::getConnexion();

$id = $_GET['id'] ?? '';

if ($id === '') {
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM user WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'accept') {
        $update = $pdo->prepare("
        UPDATE user
        SET statut = 'active'
        WHERE id = :id
    ");
        $update->execute(['id' => $id]);

        header('Location: pending_requests.php');
        exit;
    }

    if ($action === 'deny') {
        $update = $pdo->prepare("
        UPDATE user
        SET role = 'client',
            statut = 'active'
        WHERE id = :id
    ");
        $update->execute(['id' => $id]);

        header('Location: pending_requests.php?review=denied');
        exit;
    }
}

$role = strtolower(trim((string)($user['role'] ?? '')));
$experience = trim((string)($user['experience'] ?? ''));
$speciality = trim((string)($user['speciality'] ?? ''));
$motivation = trim((string)($user['motivation'] ?? ''));

$roleBadgeClass = 'role-badge-default';
if ($role === 'coach') {
    $roleBadgeClass = 'role-badge-coach';
} elseif ($role === 'nutritionist') {
    $roleBadgeClass = 'role-badge-nutritionist';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Request</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
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
            padding: 32px 20px;
        }

        .page-shell {
            max-width: 880px;
            margin: 0 auto;
        }

        .request-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
        }

        .request-head {
            margin-bottom: 24px;
        }

        .request-title {
            font-size: 30px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 8px;
        }

        .request-subtitle {
            color: #6b7280;
            line-height: 1.7;
            font-size: 15px;
            margin-bottom: 16px;
        }

        .role-badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .role-badge-default {
            background: #f3f4f6;
            color: #374151;
        }

        .role-badge-coach {
            background: #ecfdf3;
            color: #15803d;
        }

        .role-badge-nutritionist {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .request-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .request-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 18px;
            transition: 0.25s ease;
        }

        .request-box:hover {
            transform: translateY(-2px);
            border-color: #fecaca;
            background: #fffafa;
        }

        .request-box.full {
            grid-column: 1 / -1;
        }

        .label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .value {
            font-size: 15px;
            color: #111827;
            line-height: 1.75;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .empty-value {
            color: #9ca3af;
            font-style: italic;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
            margin-top: 26px;
        }

        .btn-back,
        .btn-accept,
        .btn-deny {
            border: none;
            text-decoration: none;
            padding: 13px 18px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            transition: 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-back {
            background: #ffffff;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn-back:hover {
            background: #f9fafb;
        }

        .btn-accept {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: #ffffff;
            box-shadow: 0 14px 28px rgba(22, 163, 74, 0.16);
        }

        .btn-accept:hover {
            transform: translateY(-2px);
            opacity: 0.96;
        }

        .btn-deny {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #ffffff;
            box-shadow: 0 14px 28px rgba(185, 28, 28, 0.16);
        }

        .btn-deny:hover {
            transform: translateY(-2px);
            opacity: 0.96;
        }

        @media (max-width: 768px) {
            body {
                padding: 18px;
            }

            .request-card {
                padding: 22px 18px;
            }

            .request-title {
                font-size: 25px;
            }

            .request-grid {
                grid-template-columns: 1fr;
            }

            .request-box.full {
                grid-column: auto;
            }

            .actions {
                flex-direction: column;
            }

            .btn-back,
            .btn-accept,
            .btn-deny {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="page-shell">
        <div class="request-card">
            <div class="request-head">
                <div class="request-title">Professional Request</div>
                <div class="request-subtitle">
                    Review the submitted form below before approving or denying this request.
                </div>
                <span class="role-badge <?= htmlspecialchars($roleBadgeClass) ?>">
                    <?= htmlspecialchars($role !== '' ? $role : 'request') ?>
                </span>
            </div>

            <div class="request-grid">
                <div class="request-box">
                    <span class="label">Years of Experience</span>
                    <div class="value">
                        <?php if ($experience !== ''): ?>
                            <?= htmlspecialchars($experience) ?>
                        <?php else: ?>
                            <span class="empty-value">No experience provided.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="request-box">
                    <span class="label">Speciality</span>
                    <div class="value">
                        <?php if ($speciality !== ''): ?>
                            <?= htmlspecialchars($speciality) ?>
                        <?php else: ?>
                            <span class="empty-value">No speciality provided.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="request-box full">
                    <span class="label">Motivation</span>
                    <div class="value">
                        <?php if ($motivation !== ''): ?>
                            <?= nl2br(htmlspecialchars($motivation)) ?>
                        <?php else: ?>
                            <span class="empty-value">No motivation provided.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <form method="POST" class="actions">
                <a href="pending_requests.php" class="btn-back">Back</a>
                <button type="submit" name="action" value="deny" class="btn-deny">Deny</button>
                <button type="submit" name="action" value="accept" class="btn-accept">Accept</button>
            </form>
        </div>
    </div>

</body>

</html>