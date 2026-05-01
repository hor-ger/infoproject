<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../logicals/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$hiba = "";

// Költések törlése 
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["del_id"])) {
    $id = $_POST["del_id"];
    try {
        $stmt = $pdo->prepare("DELETE FROM koltesek WHERE id = ? AND felhasznalo_id = ?");
        $stmt->execute([$id, $user_id]);
        
        // Ne küldje újra el a törlés kérését
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $hiba = "Hiba a törlés során: " . $e->getMessage();
    }
}

// Költések hozzáadása
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_submit"])) {
    $osszeg = $_POST["osszeg"];
    $kategoria_id = $_POST["kategoria_id"];
    $megjegyzes = $_POST["megjegyzes"];
    $datum = $_POST["datum"];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO koltesek (osszeg, kategoria_id, felhasznalo_id, megjegyzes, datum)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$osszeg, $kategoria_id, $user_id, $megjegyzes, $datum]);
        
        // Frissítés, hogy ne adja hozzá újra az összeget újratöltéskor
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $hiba = "Hiba a mentés során: " . $e->getMessage();
    }
}



// Kategóriák lekérése
$stmt_kat = $pdo->prepare("SELECT id, nev FROM kategoriak WHERE felhasznalo_id = ?");
$stmt_kat->execute([$user_id]);
$kategoriak = $stmt_kat->fetchAll(PDO::FETCH_ASSOC);

// Költések lekérése
$stmt_list = $pdo->prepare("
    SELECT koltesek.*, kategoriak.nev AS kategoria_nev
    FROM koltesek
    JOIN kategoriak ON koltesek.kategoria_id = kategoriak.id
    WHERE koltesek.felhasznalo_id = ?
    ORDER BY datum DESC
");
$stmt_list->execute([$user_id]);
$koltesek = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Költések</title>
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
    <?php if ($hiba !== ""): ?>
        <div><?= htmlspecialchars($hiba) ?></div>
    <?php endif; ?>

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
        
        <button type="submit" name="add_submit">Hozzáadás</button>
    </form>

    <hr>

    <h2>Eddigi költések</h2>
    <table>
        <thead>
            <tr>
                <th>Összeg</th>
                <th>Kategória</th>
                <th>Megjegyzés</th>
                <th>Dátum</th>
                <th>Művelet</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($koltesek) > 0): ?>
                <?php foreach ($koltesek as $k): ?>
                <tr>
                    <td><?= htmlspecialchars($k["osszeg"]) ?> Ft</td>
                    <td><?= htmlspecialchars($k["kategoria_nev"]) ?></td>
                    <td><?= htmlspecialchars($k["megjegyzes"]) ?></td>
                    <td><?= htmlspecialchars($k["datum"]) ?></td>
                    <td>
        <a href="szerkesztes.php?id=<?= $k['id'] ?>"><button>Szerkesztés</button></a>
<form method="POST" style="display:inline;" onsubmit="return confirm('Biztosan törölni szeretnéd ezt a költést?  Ez a művelet nem vonható vissza!');">
    <input type="hidden" name="del_id" value="<?= $k["id"] ?>">
    <button type="submit">Törlés</button>
</form></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nincs még rögzített költés.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>