<?php
session_start();

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

<a href="logout.php">Kijelentkezés</a>

<hr>

<h2>Költéseid</h2>

<!-- ide jön később:
     - kategóriák
     - költések
     - statisztika
-->

</body>
</html>