<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../logicals/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Hol a legolcsóbb?</title>
</head>
<body>

<h1>Bejelentkezett felhasználó:<?= htmlspecialchars($_SESSION["username"]) ?> </h1>
<nav>
    <ul>
        <li><a href="../logicals/dashboard.php">Dashboard</a></li>
        <li><a href="../public/profil.php">Profil</a></li>
        <li><a href="../public/kategoriak.php">Kategóriák</a></li>
        <li><a href="../public/koltesek.php">Költések</a></li>
        <li><a href="../public/legolcsobb.php">Hol a legolcsóbb?</a></li>
        <li><a href="mi.php">AI segítség</a></li>
        <li><a href="../logicals/logout.php">Kijelentkezés</a></li>
    </ul>
</nav>

<h1>Hol a legolcsóbb az adott termék?</h1>

<?php
try {
    $kat_stmt = $pdo->query("SELECT DISTINCT kat_nev FROM termekek WHERE kat_nev IS NOT NULL AND kat_nev != '' ORDER BY kat_nev ASC");
    $kategoriak = $kat_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Hiba a kategóriák betöltésekor: " . $e->getMessage());
}
?>

<form method="GET" onsubmit="return false;">
    <input type="text" id="kereso_mezo" placeholder="Termék keresése" autocomplete="off">
    
    <select id="kategoria_szuro">
        <option value="">Összes kategória</option>
        <?php foreach ($kategoriak as $kat): ?>
            <option value="<?= htmlspecialchars($kat['kat_nev']) ?>">
                <?= htmlspecialchars($kat['kat_nev']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<table border="1">
    <thead>
        <tr>
            <th>Név</th>
            <th>Kategória</th>
            <th>Egység</th>
            <th>Kiszerelés</th>
            <th>Üzlet</th>
            <th>Min ár</th>
            <th>Max ár</th>
            <th>Min egységár</th>
            <th>Max egységár</th>
        </tr>
    </thead>
    <tbody id="eredmenyek">
        <!-- Itt a JS fogja megjeleníteni az adatokat -->
    </tbody>
</table>

<script src="legolcsobb.js"></script>
</body>
</html>