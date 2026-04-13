<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin - SmartMeal') ?></title>

    <!-- Google Fonts (same as front) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Amatic+SC:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Yummy template CSS -->
    <link href="../../view/assets/css/main.css" rel="stylesheet">

    <style>
        /* ── CSS Variables (match Yummy) ── */
        :root {
            --accent:          #ce1212;
            --sidebar-width:   260px;
            --sidebar-bg:      #1a1f2e;
            --sidebar-text:    #c8cfe0;
            --heading-font:    'Amatic SC', sans-serif;
            --default-font:    'Roboto', sans-serif;
            --nav-font:        'Inter', sans-serif;
        }

        body {
            background: #f4f6fb;
            font-family: var(--default-font);
        }

        /* ── Sidebar ── */
        .admin-sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .sidebar-brand {
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,.1);
            padding: 1.2rem 1.5rem;
            font-family: var(--heading-font);
            font-size: 1.6rem;
            letter-spacing: 1px;
        }
        .sidebar-brand span { color: var(--accent); }
        .sidebar-divider { border-color: rgba(255,255,255,.1); margin: 0; }
        .admin-sidebar .nav-link {
            color: var(--sidebar-text);
            border-radius: 8px;
            padding: .6rem 1rem;
            font-family: var(--nav-font);
            font-size: .9rem;
            transition: background .2s, color .2s;
            display: flex;
            align-items: center;
            gap: .6rem;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: var(--accent);
            color: #fff;
        }
        .admin-sidebar .nav-link i { font-size: 1rem; }

        /* ── Main content ── */
        .admin-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* ── Topbar ── */
        .admin-topbar {
            background: #fff;
            border-bottom: 2px solid var(--accent);
            padding: .9rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
        }
        .admin-topbar h5 {
            font-family: var(--heading-font);
            font-size: 1.6rem;
            color: #37373f;
            margin: 0;
        }

        /* ── Content area ── */
        .admin-content { padding: 2rem; }

        /* ── Stat cards ── */
        .stat-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 3px 15px rgba(0,0,0,.08);
            transition: transform .2s;
            overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-card .icon-box {
            width: 60px; height: 60px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
        }
        .stat-card .stat-value {
            font-family: var(--heading-font);
            font-size: 2.2rem;
            color: #37373f;
        }

        /* ── Tables ── */
        .admin-table thead th {
            background: #f8f9fc;
            font-family: var(--nav-font);
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6c757d;
            border-bottom: 2px solid #e9ecef;
        }
        .admin-table td { vertical-align: middle; }
        .admin-table tbody tr:hover { background: #fff8f8; }

        /* ── Cards ── */
        .admin-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 3px 15px rgba(0,0,0,.07);
            overflow: hidden;
        }
        .admin-card .card-header {
            background: #fff;
            border-bottom: 2px solid #f0f0f0;
            padding: 1rem 1.5rem;
            font-family: var(--heading-font);
            font-size: 1.3rem;
            color: #37373f;
        }

        /* ── Buttons ── */
        .btn-yummy {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: var(--nav-font);
            font-weight: 600;
            padding: .5rem 1.2rem;
            transition: background .2s, transform .1s;
        }
        .btn-yummy:hover { background: #a50e0e; color: #fff; transform: translateY(-1px); }

        /* ── Image upload zone ── */
        .drop-zone {
            border: 2px dashed var(--accent);
            border-radius: 14px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            background: #fff8f8;
            transition: background .2s;
        }
        .drop-zone:hover { background: #fde8e8; }

        /* ── Badges ── */
        .badge-facile   { background: #198754 !important; }
        .badge-moyen    { background: #fd7e14 !important; }
        .badge-difficile{ background: var(--accent) !important; }

        /* ── Alerts ── */
        .alert-auto { animation: fadeOut 0.5s ease 3.5s forwards; }
        @keyframes fadeOut { to { opacity: 0; height: 0; padding: 0; margin: 0; overflow: hidden; } }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); transition: transform .3s; }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-main { margin-left: 0; }
        }
    </style>
</head>
<body>
