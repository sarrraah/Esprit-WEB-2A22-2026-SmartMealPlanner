<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin - SmartMeal') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Amatic+SC:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../../view/assets/css/main.css" rel="stylesheet">
    <style>
        :root { --accent:#ce1212; --sidebar-width:260px; --sidebar-bg:#1a1f2e; }
        body { background:#f4f6fb; font-family:'Roboto',sans-serif; }

        /* Sidebar */
        .admin-sidebar {
            width:var(--sidebar-width); min-height:100vh;
            background:var(--sidebar-bg); color:#c8cfe0;
            position:fixed; top:0; left:0; z-index:1000;
            overflow-y:auto; display:flex; flex-direction:column;
        }
        .sidebar-brand {
            color:#fff; padding:1.2rem 1.5rem;
            border-bottom:1px solid rgba(255,255,255,.1);
            font-family:'Amatic SC',sans-serif; font-size:1.8rem; letter-spacing:1px;
        }
        .sidebar-brand span { color:var(--accent); }
        .sidebar-divider { border-color:rgba(255,255,255,.1); margin:0; }
        .admin-sidebar .nav-link {
            color:#c8cfe0; border-radius:8px; padding:.6rem 1rem;
            font-family:'Inter',sans-serif; font-size:.88rem;
            transition:background .2s,color .2s;
            display:flex; align-items:center; gap:.6rem;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active { background:var(--accent); color:#fff; }

        /* Main */
        .admin-main { margin-left:var(--sidebar-width); min-height:100vh; }

        /* Topbar */
        .admin-topbar {
            background:#fff; border-bottom:2px solid var(--accent);
            padding:.9rem 2rem; display:flex; align-items:center;
            justify-content:space-between; position:sticky; top:0; z-index:100;
            box-shadow:0 2px 10px rgba(0,0,0,.06);
        }
        .admin-topbar h5 {
            font-family:'Amatic SC',sans-serif; font-size:1.7rem;
            color:#37373f; margin:0;
        }

        /* Content */
        .admin-content { padding:2rem; }

        /* Stat cards */
        .stat-card {
            border:none; border-radius:14px;
            box-shadow:0 3px 15px rgba(0,0,0,.08);
            transition:transform .2s; overflow:hidden;
        }
        .stat-card:hover { transform:translateY(-4px); }
        .stat-card .icon-box {
            width:60px; height:60px; border-radius:14px;
            display:flex; align-items:center; justify-content:center; font-size:1.6rem;
        }
        .stat-card .stat-value {
            font-family:'Amatic SC',sans-serif; font-size:2.4rem; color:#37373f;
        }

        /* Admin cards */
        .admin-card { border:none; border-radius:14px; box-shadow:0 3px 15px rgba(0,0,0,.07); overflow:hidden; }
        .admin-card .card-header {
            background:#fff; border-bottom:2px solid #f0f0f0;
            padding:1rem 1.5rem; font-family:'Amatic SC',sans-serif;
            font-size:1.3rem; color:#37373f;
        }

        /* Tables */
        .admin-table thead th {
            background:#f8f9fc; font-family:'Inter',sans-serif;
            font-size:.75rem; text-transform:uppercase;
            letter-spacing:.08em; color:#6c757d;
            border-bottom:2px solid #e9ecef;
        }
        .admin-table td { vertical-align:middle; }
        .admin-table tbody tr:hover { background:#fff8f8; }

        /* Buttons */
        .btn-yummy {
            background:var(--accent); color:#fff; border:none;
            border-radius:8px; font-family:'Inter',sans-serif;
            font-weight:600; transition:background .2s,transform .1s;
        }
        .btn-yummy:hover { background:#a50e0e; color:#fff; transform:translateY(-1px); }

        /* Drop zone */
        .drop-zone {
            border:2px dashed var(--accent); border-radius:14px;
            padding:2rem; text-align:center; cursor:pointer;
            background:#fff8f8; transition:background .2s;
        }
        .drop-zone:hover { background:#fde8e8; }

        /* Badges */
        .badge-facile    { background:#198754 !important; }
        .badge-moyen     { background:#fd7e14 !important; }
        .badge-difficile { background:var(--accent) !important; }

        /* Alert auto-dismiss */
        .alert-auto { animation:fadeOut .5s ease 3.5s forwards; }
        @keyframes fadeOut { to { opacity:0; height:0; padding:0; margin:0; overflow:hidden; } }

        @media(max-width:768px) {
            .admin-sidebar { transform:translateX(-100%); }
            .admin-main { margin-left:0; }
        }
    </style>
</head>
<body>
