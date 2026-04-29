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
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="../logicals/profil.php">Profil</a></li>
        <li><a href="../public/kategoriak.php">Kategóriák</a></li>
        <li><a href="../public/koltesek.php">Költések</a></li>
        <li><a href="../public/legolcsobb.php">Hol a legolcsóbb?</a></li>
        <li><a href="logout.php">Kijelentkezés</a></li>
    </ul>
</nav>

<hr>


<?php
$ev = $_GET["ev"] ?? date('Y');
$honap = $_GET["honap"] ?? date('n');

$stmt = $pdo->prepare("
    SELECT koltesek.*, kategoriak.nev AS kategoria_nev
    FROM koltesek
    JOIN kategoriak ON koltesek.kategoria_id = kategoriak.id
    WHERE koltesek.felhasznalo_id = ?
    AND YEAR(koltesek.datum) = ?
    AND MONTH(koltesek.datum) = ?
    ORDER BY koltesek.datum DESC
");

$stmt->execute([$_SESSION["user_id"], $ev, $honap]);
$koltesek = $stmt->fetchAll(PDO::FETCH_ASSOC);

$honapok = [
    1 => "Január", 2 => "Február", 3 => "Március",
    4 => "Április", 5 => "Május", 6 => "Június",
    7 => "Július", 8 => "Augusztus", 9 => "Szeptember",
    10 => "Október", 11 => "November", 12 => "December"
];
?>


<form method="GET">

    <select name="ev">
        <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
            <option value="<?= $i ?>" <?= ($i == date('Y') ? 'selected' : '') ?>>
                <?= $i ?>
            </option>
        <?php endfor; ?>
    </select>

    <select name="honap">
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= ($m == date('n') ? 'selected' : '') ?>>
                <?= $m ?>
            </option>
        <?php endfor; ?>
    </select>

    <button type="submit">Szűrés</button>
</form>

<h3><?=$ev ?> <?= $honapok[$honap] ?> havi költések</h3>

<table>
<tr>
    <th>Dátum</th>
    <th>Kategória</th>
    <th>Összeg</th>
    <th>Megjegyzés</th>
</tr>

<?php foreach ($koltesek as $k): ?>
<tr>
    <td><?= $k["datum"] ?></td>
    <td><?= htmlspecialchars($k["kategoria_nev"]) ?></td>
    <td><?= $k["osszeg"] ?> Ft</td>
    <td><?= htmlspecialchars($k["megjegyzes"]) ?></td>
</tr>
<?php endforeach; ?>
</table>

<?php
$stmt = $pdo->prepare("
    SELECT SUM(osszeg) as ossz
    FROM koltesek
    WHERE felhasznalo_id = ?
    AND YEAR(datum) = ?
    AND MONTH(datum) = ?
");

$stmt->execute([$_SESSION["user_id"], $ev, $honap]);
$ossz = $stmt->fetch()["ossz"];
?>

<p>Költés összesen: <?= $ossz ?? 0 ?> Ft</p>
</body>
</html>