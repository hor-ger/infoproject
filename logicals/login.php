<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["felhasznalonev"]);
    $password = $_POST["jelszo"];

    $stmt = $pdo->prepare("SELECT * FROM felhasznalok WHERE felhasznalonev = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["jelszo"])) {

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["felhasznalonev"];

        header("Location: dashboard.php");
        exit;

    } else {
        echo "Hibás belépési adatok!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Belépés</title>
</head>
<body>

<nav>
    <ul>
        <li><a href="../logicals/dashboard.php">Dashboard</a></li>
        <li><a href="profil.php">Profil</a></li>
        <li><a href="kategoriak.php">Kategóriák</a></li>
        <li><a href="koltesek.php">Költések</a></li>
        <li><a href="legolcsobb.php">Hol a legolcsóbb?</a></li>
        <li><a href="mi.php">AI segítség</a></li>
    </ul>
</nav>

<h1>Belépés</h1>
<form method="POST">
    <input type="text" name="felhasznalonev" placeholder="Felhasználónév" required>
    <input type="password" name="jelszo" placeholder="Jelszó" required>
    <button type="submit">Bejelentkezés</button>
</form>

<p>Nem vagy még felhasználó? <a href="register.php">Ide kattintva regisztrálhatsz</a></p>
</body>
</html>
