<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../logicals/login.php");
    exit;
}
$user_id = $_SESSION["user_id"];
$teljes_nev = $email = $reg_datum = $fnev = "";

try {
    $stmt = $pdo->prepare("SELECT teljnev, email, letrehozva, felhasznalonev FROM felhasznalok WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        $teljes_nev = $user['teljnev'];
        $email      = $user['email'];
        $reg_datum  = $user['letrehozva'];
        $fnev       = $user['felhasznalonev'];
    }
} catch (PDOException $e) {
    $hiba = "Hiba az adatok lekérésekor: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
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

<h1>Profil részletei</h1>
<ul>
<li>Felhasználónév: <?php echo htmlspecialchars($fnev); ?></li>
<li>Teljes név: <?php echo htmlspecialchars($teljes_nev); ?></li>
<li>Email cím: <?php echo htmlspecialchars($email); ?></li>
<li>Regisztráció ideje: <?php echo htmlspecialchars($reg_datum); ?></li>
<ul>

<h3>Jelszó megváltoztatása<h3>
<form action="jelszo.php" method="POST">
    <input type="password" name="regi_jelszo" placeholder="régi jelszó" required><br>
    <input type="password" name="uj_jelszo" placeholder="új jelszó" required><br>
    <input type="password" name="uj_jelszo_ujra" placeholder="új jelszó újra" required><br>
    <button type="submit">Jelszó módosítása</button>
</form>

<form action="torles.php" method="POST" onsubmit="return confirm('Biztosan törölni szeretnéd a profilodat? Ez a művelet nem vonható vissza!');">
    <button type="submit" name="delete_user">Profil törlése</button>
</form>

</body>
</html>