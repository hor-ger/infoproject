<?php
session_start();
require_once "../config/database.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>

<h1>Bejelentkezett felhasználó:<?= htmlspecialchars($_SESSION["username"]) ?>! </h1>
<nav>
    <ul>
        <li><a href="../logicals/dashboard.php">Dashboard</a></li>
        <li><a href="kategoriak.php">Kategóriák</a></li>
        <li><a href="koltesek.php">Költések</a></li>
        <li><a href="logout.php">Kijelentkezés</a></li>
    </ul>
</nav>

<hr>

<h2>Költések</h2>

</body>
</html>