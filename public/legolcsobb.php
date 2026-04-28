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

<?php
$kereses = $_GET["kereses"] ?? "";

$stmt = $pdo->prepare("
    SELECT *
    FROM termekek
    WHERE termeknev LIKE ?
    ORDER BY min_ar ASC
    LIMIT 25
");

$stmt->execute(["%$kereses%"]);
$talalatok = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="GET">
    <input type="text" name="kereses" placeholder="Termék keresése">
    <button>Keresés</button>
</form>

<table border="1">
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

<?php foreach ($talalatok as $t): ?>
<tr>
    <td><?= htmlspecialchars($t["termeknev"]) ?></td>
    <td><?= htmlspecialchars($t["kat_nev"]) ?></td>
    <td><?= htmlspecialchars($t["egyseg"]) ?></td>
    <td><?= htmlspecialchars($t["kiszereles"]) ?></td>
    <td><?= htmlspecialchars($t["uzlet"]) ?></td>
    <td><?= htmlspecialchars($t["min_ar"]) ?> Ft</td>
    <td><?= htmlspecialchars($t["max_ar"]) ?> Ft</td>
    <td><?= htmlspecialchars($t["min_egysegar"]) ?> Ft</td>
    <td><?= htmlspecialchars($t["max_egysegar"]) ?> Ft</td>
</tr>
<?php endforeach; ?>
</table>

<hr>
</body>
</html>