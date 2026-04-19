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
        <li><a href="legolcsobb.php">Hol a legolcsóbb?</a></li>
        <li><a href="../logicals/logout.php">Kijelentkezés</a></li>
    </ul>
</nav>

<hr>

<!-- Kategóriák lekérése az adott felhasználóhoz -->
<?php
$stmt = $pdo->prepare("
    SELECT id, nev FROM kategoriak
    WHERE felhasznalo_id = ?
");
$stmt->execute([$_SESSION["user_id"]]);
$kategoriak = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Új költés hozzáadása</h3>

<form method="POST">
    <input type="number" name="osszeg" placeholder="Összeg" required><br><br>
    <select name="kategoria_id" required>
        <option value="">Válassz kategóriát</option>
        <?php foreach ($kategoriak as $kat): ?>
            <option value="<?= $kat["id"] ?>">
                <?= htmlspecialchars($kat["nev"]) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>
    <input type="text" name="megjegyzes" placeholder="Megjegyzés"><br><br>
    <input type="date" name="datum" required value="<?= date('Y-m-d') ?>"><br><br>
    <button type="submit">Hozzáadás</button>
</form>

<!-- Költés hozzáadása az adatbázishoz az adott felhasználónak -->
 
 <?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $osszeg = $_POST["osszeg"];
    $kategoria_id = $_POST["kategoria_id"];
    $megjegyzes = $_POST["megjegyzes"];
    $datum = $_POST["datum"];
    $user_id = $_SESSION["user_id"];

    $stmt = $pdo->prepare("
        INSERT INTO koltesek 
        (osszeg, kategoria_id, felhasznalo_id, megjegyzes, datum)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $osszeg,
        $kategoria_id,
        $user_id,
        $megjegyzes,
        $datum
    ]);
}
?>

<h2>eddigi költések</h2>
<!-- Költések kilistázása az adatbázisból-->
<?php
$stmt = $pdo->prepare("
    SELECT koltesek.*, kategoriak.nev AS kategoria_nev
    FROM koltesek
    JOIN kategoriak ON koltesek.kategoria_id = kategoriak.id
    WHERE koltesek.felhasznalo_id = ?
    ORDER BY datum DESC
");

$stmt->execute([$_SESSION["user_id"]]);
$koltesek = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Költések kilistázása egy táblába-->

<table>
<tr>
    <th>Összeg</th>
    <th>Kategória</th>
    <th>Megjegyzés</th>
    <th>Dátum</th>
</tr>

<?php foreach ($koltesek as $k): ?>
<tr>
    <td><?= $k["osszeg"] ?></td>
    <td><?= htmlspecialchars($k["kategoria_nev"]) ?></td>
    <td><?= htmlspecialchars($k["megjegyzes"]) ?></td>
    <td><?= $k["datum"] ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>