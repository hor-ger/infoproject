<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../logicals/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_termek_id"])) {
    $termek_id = (int) $_POST["add_termek_id"];
    $returnPage = isset($_POST["current_page"]) ? max(1, (int) $_POST["current_page"]) : 1;

    try {
        $termekStmt = $pdo->prepare("SELECT termeknev, kat_nev, min_ar FROM termekek WHERE id = ?");
        $termekStmt->execute([$termek_id]);
        $termek = $termekStmt->fetch(PDO::FETCH_ASSOC);

        if (!$termek) {
            throw new Exception("A termék nem található.");
        }

        $katNev = trim($termek["kat_nev"] ?: "Egyéb");
        $katStmt = $pdo->prepare("SELECT id FROM kategoriak WHERE felhasznalo_id = ? AND nev = ?");
        $katStmt->execute([$user_id, $katNev]);
        $kat = $katStmt->fetch(PDO::FETCH_ASSOC);

        if ($kat) {
            $kategoria_id = $kat["id"];
        } else {
            $insertKat = $pdo->prepare("INSERT INTO kategoriak (nev, felhasznalo_id) VALUES (?, ?)");
            $insertKat->execute([$katNev, $user_id]);
            $kategoria_id = $pdo->lastInsertId();
        }

        $osszeg = (int) round($termek["min_ar"]);
        if ($osszeg <= 0) {
            $osszeg = 1;
        }

        $megjegyzes = "Termék: " . $termek["termeknev"];
        $datum = date("Y-m-d");

        $insert = $pdo->prepare("INSERT INTO koltesek (osszeg, kategoria_id, felhasznalo_id, megjegyzes, datum) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$osszeg, $kategoria_id, $user_id, $megjegyzes, $datum]);

        header("Location: ?page=" . $returnPage . "&added=1");
        exit;
    } catch (Exception $e) {
        $error = "Hiba hozzáadáskor: " . $e->getMessage();
    }
}

if (isset($_GET["added"])) {
    $success = "A termék hozzáadva a költésekhez.";
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Termékek</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<h1 class="kozepre">Bejelentkezett felhasználó: <?= htmlspecialchars($_SESSION["username"]) ?> </h1>
<nav>
    <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="../logicals/dashboard.php">Dashboard</a></li>
        <li><a href="../public/profil.php">Profil</a></li>
        <li><a href="../public/kategoriak.php">Kategóriák</a></li>
        <li><a href="../public/koltesek.php">Költések</a></li>
        <li><a href="../public/legolcsobb.php">Termékek</a></li>
        <li><a href="mi.php">AI segítség</a></li>
        <li><a href="../logicals/logout.php">Kijelentkezés</a></li>
    </ul>
</nav>

<h1>Termékek</h1>

<form class="search-form" method="GET">
    <input type="text" name="nev" placeholder="Termék neve" value="<?= htmlspecialchars($_GET['nev'] ?? '') ?>" onblur="this.form.submit()">
    <input type="text" name="kategoria" placeholder="Kategória" value="<?= htmlspecialchars($_GET['kategoria'] ?? '') ?>" onblur="this.form.submit()">
    <input type="text" name="egyseg" placeholder="Egység" value="<?= htmlspecialchars($_GET['egyseg'] ?? '') ?>" onblur="this.form.submit()">
    <input type="text" name="kiszereles" placeholder="Kiszerelés" value="<?= htmlspecialchars($_GET['kiszereles'] ?? '') ?>" onblur="this.form.submit()">
    <input type="text" name="uzlet" placeholder="Üzlet" value="<?= htmlspecialchars($_GET['uzlet'] ?? '') ?>" onblur="this.form.submit()">
    <input type="number" name="min_ar" placeholder="Min ár" value="<?= htmlspecialchars($_GET['min_ar'] ?? '') ?>" step="0.01" onchange="this.form.submit()">
    <input type="number" name="max_ar" placeholder="Max ár" value="<?= htmlspecialchars($_GET['max_ar'] ?? '') ?>" step="0.01" onchange="this.form.submit()">
    <input type="number" name="min_egysegar" placeholder="Min egységár" value="<?= htmlspecialchars($_GET['min_egysegar'] ?? '') ?>" step="0.01" onchange="this.form.submit()">
    <input type="number" name="max_egysegar" placeholder="Max egységár" value="<?= htmlspecialchars($_GET['max_egysegar'] ?? '') ?>" step="0.01" onchange="this.form.submit()">
    <button type="submit" style="font-size: 0.9em; padding: 5px 10px;">Keresés</button>
    <button type="button" onclick="window.location.href='?'" style="font-size: 0.9em; padding: 5px 10px;">Törlés</button>
</form>

<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php
$limit = 100;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if (!empty($_GET['nev'])) {
    $where[] = "termeknev LIKE ?";
    $params[] = '%' . $_GET['nev'] . '%';
}
if (!empty($_GET['kategoria'])) {
    $where[] = "kat_nev LIKE ?";
    $params[] = '%' . $_GET['kategoria'] . '%';
}
if (!empty($_GET['egyseg'])) {
    $where[] = "egyseg LIKE ?";
    $params[] = '%' . $_GET['egyseg'] . '%';
}
if (!empty($_GET['kiszereles'])) {
    $where[] = "kiszereles LIKE ?";
    $params[] = '%' . $_GET['kiszereles'] . '%';
}
if (!empty($_GET['uzlet'])) {
    $where[] = "uzlet LIKE ?";
    $params[] = '%' . $_GET['uzlet'] . '%';
}
if (!empty($_GET['min_ar'])) {
    $where[] = "min_ar >= ?";
    $params[] = (float) $_GET['min_ar'];
}
if (!empty($_GET['max_ar'])) {
    $where[] = "max_ar <= ?";
    $params[] = (float) $_GET['max_ar'];
}
if (!empty($_GET['min_egysegar'])) {
    $where[] = "min_egysegar >= ?";
    $params[] = (float) $_GET['min_egysegar'];
}
if (!empty($_GET['max_egysegar'])) {
    $where[] = "max_egysegar <= ?";
    $params[] = (float) $_GET['max_egysegar'];
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM termekek $whereClause");
    for ($i = 0; $i < count($params); $i++) {
        $countStmt->bindValue($i + 1, $params[$i], is_numeric($params[$i]) ? PDO::PARAM_STR : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($total / $limit));

    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $limit;
    }

    $stmt = $pdo->prepare(
        "SELECT id, termeknev, kat_nev, uzlet, egyseg, kiszereles, min_ar, max_ar, min_egysegar, max_egysegar
         FROM termekek
         $whereClause
         ORDER BY id ASC
         LIMIT ? OFFSET ?"
    );
    $numWhere = count($params);
    for ($i = 0; $i < $numWhere; $i++) {
        $stmt->bindValue($i + 1, $params[$i], is_numeric($params[$i]) ? PDO::PARAM_STR : PDO::PARAM_STR);
    }
    $stmt->bindValue($numWhere + 1, $limit, PDO::PARAM_INT);
    $stmt->bindValue($numWhere + 2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $termekek = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Hiba a termékek betöltésekor: " . $e->getMessage());
}
?>

<?php if (count($termekek) === 0): ?>
    <p>Nincs elérhető termék.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Név</th>
                <th>Kategória</th>
                <th>Egység</th>
                <th>Kiszerelés</th>
                <th>Üzlet</th>
                <th>Min ár</th>
                <th>Max ár</th>
                <th>Min egységár</th>
                <th>Max egységár</th>
                <th>Hozzáadás</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($termekek as $termek): ?>
                <tr>
                    <td><?= htmlspecialchars($termek['id']) ?></td>
                    <td><?= htmlspecialchars($termek['termeknev']) ?></td>
                    <td><?= htmlspecialchars($termek['kat_nev']) ?></td>
                    <td><?= htmlspecialchars($termek['egyseg']) ?></td>
                    <td><?= htmlspecialchars($termek['kiszereles']) ?></td>
                    <td><?= htmlspecialchars($termek['uzlet']) ?></td>
                    <td><?= htmlspecialchars($termek['min_ar']) ?> Ft</td>
                    <td><?= htmlspecialchars($termek['max_ar']) ?> Ft</td>
                    <td><?= htmlspecialchars($termek['min_egysegar']) ?> Ft</td>
                    <td><?= htmlspecialchars($termek['max_egysegar']) ?> Ft</td>
                    <td>
                        <form method="POST" style="display:inline; margin: 0; padding: 0;">
                            <input type="hidden" name="add_termek_id" value="<?= htmlspecialchars($termek['id']) ?>">
                            <input type="hidden" name="current_page" value="<?= htmlspecialchars($page) ?>">
                            <button type="submit">+</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=1">&laquo; Első</a>
            <a href="?page=<?= $page - 1 ?>">Előző</a>
        <?php endif; ?>

        <span class="current">Oldal <?= $page ?> / <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>">Következő</a>
            <a href="?page=<?= $totalPages ?>">Utolsó &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>
