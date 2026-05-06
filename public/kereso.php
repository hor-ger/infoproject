<?php
session_start();
require_once "../config/database.php";

$kereses = $_GET["kereses"] ?? "";
$kategoria = $_GET["kategoria"] ?? "";

$sql = "SELECT * FROM termekek WHERE termeknev LIKE ?";
$params = ["%$kereses%"];

if (!empty($kategoria)) {
    $sql .= " AND kat_nev = ?"; 
    $params[] = $kategoria;
}

$sql .= " ORDER BY min_ar ASC LIMIT 25";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$talalatok = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($talalatok)) {
    echo "<tr><td colspan='9'>Nincs találat.</td></tr>";
} else {
    foreach ($talalatok as $t): ?>
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
    <?php endforeach;
}
?>