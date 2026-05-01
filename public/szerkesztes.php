<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"]) || !isset($_GET["id"])) {
    header("Location: koltesek.php");
    exit;
}

$id = $_GET["id"];
$user_id = $_SESSION["user_id"];
$uzenet = "";

// Mentés
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_submit"])) {
    $osszeg = $_POST["osszeg"];
    $kategoria_id = $_POST["kategoria_id"];
    $megjegyzes = $_POST["megjegyzes"];
    $datum = $_POST["datum"];

    try {
        $stmt = $pdo->prepare("
            UPDATE koltesek 
            SET osszeg = ?, kategoria_id = ?, megjegyzes = ?, datum = ?
            WHERE id = ? AND felhasznalo_id = ?
        ");
        $stmt->execute([$osszeg, $kategoria_id, $megjegyzes, $datum, $id, $user_id]);
        
        header("Location: koltesek.php?siker=1");
        exit;
    } catch (PDOException $e) {
        $uzenet = "Hiba a módosítás során: " . $e->getMessage();
    }
}

// Régi adatok lekérése
$stmt = $pdo->prepare("SELECT * FROM koltesek WHERE id = ? AND felhasznalo_id = ?");
$stmt->execute([$id, $user_id]);
$koltes = $stmt->fetch();

if (!$koltes) {
    die("A költés nem található.");
}

// Kategóriák lekérése
$stmt_kat = $pdo->prepare("SELECT id, nev FROM kategoriak WHERE felhasznalo_id = ?");
$stmt_kat->execute([$user_id]);
$kategoriak = $stmt_kat->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Költés szerkesztése</title>
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

    <h1>Költés szerkesztése</h1>
    <p><?= $uzenet ?></p>

    <form method="POST">
        <label>Összeg:</label><br>
        <input type="number" name="osszeg" value="<?= htmlspecialchars($koltes['osszeg']) ?>" required><br><br>

        <label>Kategória:</label><br>
        <select name="kategoria_id" required>
            <?php foreach ($kategoriak as $kat): ?>
                <option value="<?= $kat['id'] ?>" <?= $kat['id'] == $koltes['kategoria_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kat['nev']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Megjegyzés:</label><br>
        <input type="text" name="megjegyzes" value="<?= htmlspecialchars($koltes['megjegyzes']) ?>"><br><br>

        <label>Dátum:</label><br>
        <input type="date" name="datum" value="<?= htmlspecialchars($koltes['datum']) ?>" required><br><br>

        <button type="submit" name="update_submit">Módosítások mentése</button>
        <a href="koltesek.php">Mégse</a>
    </form>
</body>
</html>