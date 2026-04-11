<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestionEvent – BackOffice</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #F0F2F5; color: #333; }

        /* ── Navbar ── */
        nav {
            background: linear-gradient(135deg, #1A1A2E 0%, #16213E 100%);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
            box-shadow: 0 2px 10px rgba(0,0,0,.4);
        }
        nav .brand { color: #E94560; font-size: 1.4rem; font-weight: 700; text-decoration: none; }
        nav ul { list-style: none; display: flex; gap: 10px; }
        nav ul li a {
            color: #ccc; text-decoration: none; padding: 8px 14px;
            border-radius: 6px; font-size: .9rem; transition: background .2s, color .2s;
        }
        nav ul li a:hover, nav ul li a.active {
            background: #E94560; color: #fff;
        }

        /* ── Page wrapper ── */
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }

        /* ── Cards / tables ── */
        .card {
            background: #fff; border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08); padding: 28px; margin-bottom: 24px;
        }
        .card h2 { font-size: 1.2rem; margin-bottom: 20px; color: #1A1A2E; border-left: 4px solid #E94560; padding-left: 10px; }

        table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        thead tr { background: #1A1A2E; color: #fff; }
        thead th { padding: 12px 14px; text-align: left; }
        tbody tr:nth-child(even) { background: #F8F9FA; }
        tbody td { padding: 10px 14px; border-bottom: 1px solid #eee; vertical-align: middle; }

        /* ── Buttons ── */
        .btn {
            display: inline-block; padding: 7px 14px; border-radius: 6px;
            font-size: .82rem; font-weight: 600; text-decoration: none;
            cursor: pointer; border: none; transition: opacity .2s;
        }
        .btn:hover { opacity: .85; }
        .btn-primary  { background: #4A90D9; color: #fff; }
        .btn-success  { background: #27AE60; color: #fff; }
        .btn-danger   { background: #E74C3C; color: #fff; }
        .btn-warning  { background: #F39C12; color: #fff; }
        .btn-info     { background: #8E44AD; color: #fff; }

        /* ── Forms ── */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: .9rem; color: #555; }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd;
            border-radius: 7px; font-size: .9rem; background: #FAFAFA;
            transition: border-color .2s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none; border-color: #4A90D9; background: #fff;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* ── Alerts ── */
        .alert { padding: 12px 18px; border-radius: 7px; margin-bottom: 18px; font-size: .9rem; }
        .alert-success { background: #D5F5E3; color: #1E8449; border: 1px solid #A9DFBF; }
        .alert-danger  { background: #FADBD8; color: #922B21; border: 1px solid #F1948A; }

        /* ── Badges ── */
        .badge {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: .78rem; font-weight: 700;
        }
        .badge-confirme  { background: #D5F5E3; color: #1E8449; }
        .badge-refuse    { background: #FADBD8; color: #922B21; }
        .badge-attente   { background: #FEF9E7; color: #B7950B; }
        .badge-active    { background: #D6EAF8; color: #1A5276; }
        .badge-annule    { background: #FADBD8; color: #922B21; }
        .badge-termine   { background: #EAECEE; color: #555; }
    </style>
</head>
<body>
<nav>
    <a class="brand" href="#">🎭 GestionEvent</a>
    <ul>
        <li><a href="../View/BackOffice/listEvenements.php">Événements</a></li>
        <li><a href="../View/BackOffice/addEvenement.php">+ Ajouter Événement</a></li>
        <li><a href="../View/BackOffice/listParticipations.php">Participations</a></li>
        <li><a href="../View/BackOffice/addParticipation.php">+ Ajouter Participation</a></li>
    </ul>
</nav>
