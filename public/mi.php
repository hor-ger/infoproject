<?php
session_start();
require_once "../config/database.php";
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>AI segítség</title>
</head>
<body>

<h1>Bejelentkezett felhasználó:<?= htmlspecialchars($_SESSION["username"]) ?> </h1>
<nav>
    <ul>
        <li><a href="../logicals/dashboard.php">Dashboard</a></li>
        <li><a href="profil.php">Profil</a></li>
        <li><a href="kategoriak.php">Kategóriák</a></li>
        <li><a href="koltesek.php">Költések</a></li>
        <li><a href="legolcsobb.php">Hol a legolcsóbb?</a></li>
        <li><a href="mi.php">AI segítség</a></li>
        <li><a href="../logicals/logout.php">Kijelentkezés</a></li>
    </ul>
</nav>

<h1>AI placeholder</h1>



</body>
</html>