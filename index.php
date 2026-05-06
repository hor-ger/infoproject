<?php
session_start();
require_once __DIR__ . "/config/database.php";
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Költségkezelés</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .login-bar {
            width: 100%;
            text-align: center;
            padding: 10px;
            background: #ddd;
            margin-bottom: 20px;
        }
        .login-bar a {
            text-decoration: none;
            color: #333;
            font-size: 18px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border-radius: 5px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-template-rows: repeat(2, minmax(0, 1fr));
            height: calc(100vh - 60px);
            gap: 15px;
            padding: 10px;
            box-sizing: border-box;
        }
        .menu-item {
            width: 100%;
            height: 100%;
            background: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: none;
            border-radius: 15px;
            min-height: 0;
            text-decoration: none;
        }
        .menu-item:hover {
            background: #003a77;
        }
        .menu-item span {
            color: black;
            font-size: 50px;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
            padding: 10px;
        }
    </style>
</head>
<body>

<div class="login-bar">
    <?php if (!isset($_SESSION["user_id"])): ?>
        <a href="logicals/login.php">Bejelentkezés</a>
    <?php else: ?>
        <a href="logicals/logout.php">Kijelentkezés</a>
    <?php endif; ?>
</div>

<div class="menu-grid">
    <a class="menu-item" href="logicals/dashboard.php"><span>Dashboard</span></a>
    <a class="menu-item" href="public/profil.php"><span>Profil</span></a>
    <a class="menu-item" href="public/kategoriak.php"><span>Kategóriák</span></a>
    <a class="menu-item" href="public/koltesek.php"><span>Költések</span></a>
    <a class="menu-item" href="public/legolcsobb.php"><span>Termékek</span></a>
    <a class="menu-item" href="public/mi.php"><span>AI segítség</span></a>
</div>

</body>
</html>