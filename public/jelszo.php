<?php
session_start();
require_once "../config/database.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: ../logicals/login.php");
    exit;
}

$jelszouz = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION['user_id'];
    $regi_jelszo = $_POST['regi_jelszo'];
    $uj_jelszo = $_POST['uj_jelszo'];
    $uj_jelszo_ujra = $_POST['uj_jelszo_ujra'];

    try {
        if ($uj_jelszo !== $uj_jelszo_ujra) {
            $jelszouz = "Az új jelszavak nem egyeznek meg.";
        }
        $stmt = $pdo->prepare("SELECT jelszo FROM felhasznalok WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($regi_jelszo, $user['jelszo'])) {
            $uj_hash = password_hash($uj_jelszo, PASSWORD_DEFAULT);

            $update = $pdo->prepare("UPDATE felhasznalok SET jelszo = ? WHERE id = ?");
            $update->execute([$uj_hash, $user_id]);

            $jelszouz = "Sikeres jelszómódosítás!";
        } else {
            $jelszouz = "A jelenlegi jelszó hibás!";
        }

    } catch (PDOException $e) {
        echo "Hiba történt: " . $e->getMessage();
    }
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

<?php if (!empty($uzenet)): ?>
            <h1><?php echo $uzenet; ?></h1>
        <?php endif; ?>

</body>
</html>