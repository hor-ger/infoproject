<?php
session_start();
require_once "../config/database.php";
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Hol a legolcsóbb?</title>
</head>
<body>

<h1>Hol a legolcsóbb az adott termék?</h1>
<nav>
    <ul>
        <li><a href="../logicals/dashboard.php">Dashboard</a></li>
        <li><a href="../public/kategoriak.php">Kategóriák</a></li>
        <li><a href="../public/koltesek.php">Költések</a></li>
        <li><a href="../public/legolcsobb.php">Hol a legolcsóbb?</a></li>
        <li><a href="../logicals/logout.php">Kijelentkezés</a></li>
    </ul>
</nav>

<hr>
</body>
</html>