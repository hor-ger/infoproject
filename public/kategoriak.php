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
    <title>Kategoriák</title>
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

<hr>

<!--Kategória hozzáadása az adatbázishoz csak az adott felhasználónak, ha létezik hiba dobása -->

<h3>Új kategória hozzáadása</h3>

<form method="POST">
    <input type="text" name="kategoria_nev" placeholder="Kategória neve" required>
    <button type="submit">Hozzáadás</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nev = trim($_POST["kategoria_nev"]);
    $user_id = $_SESSION["user_id"];

    if (!empty($nev)) {

        $stmt = $pdo->prepare("
            INSERT INTO kategoriak (nev, felhasznalo_id)
            VALUES (?, ?)
        ");

        try {
        $stmt->execute([$nev, $user_id]);
    } catch (PDOException $e) {
        echo "Ez a kategória már létezik!";
        }
    }
}
?>


<!--Kategóriák lekérése, rendszerezés létrehozás alapján a legújabbtól -->
<?php
$stmt = $pdo->prepare("
    SELECT * FROM kategoriak
    WHERE felhasznalo_id = ?
    ORDER BY letrehozva DESC
");

$stmt->execute([$_SESSION["user_id"]]);
$kategoriak = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Kategóriáid</h3>


<!--Kategóriák törlése az adatbázisból -->

<?php
if (isset($_POST["delete_id"])) {

    $id = $_POST["delete_id"];
    $user_id = $_SESSION["user_id"];

    $stmt = $pdo->prepare("
        DELETE FROM kategoriak
        WHERE id = ? AND felhasznalo_id = ?
    ");

    $stmt->execute([$id, $user_id]);
}
?>


<!--Kategóriák kiírása, rendszerezés létrehozás alapján a legújabbtól, törlés gomb megjelenítése -->
<table>
<tr>
<th>Kategória neve</th>
<th>Létrehozás dátuma</th>
</tr>
<?php foreach ($kategoriak as $kat): ?>
<tr>
    <td><?= htmlspecialchars($kat["nev"]) ?></td>
    <td><?= htmlspecialchars($kat["letrehozva"]) ?></td>
    <td><form method="POST">
            <input type="hidden" name="delete_id" value="<?= $kat["id"] ?>">
            <button type="submit">Törlés</button>
        </form></td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>