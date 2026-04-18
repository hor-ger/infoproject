<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["felhasznalonev"]);
    $password = $_POST["jelszo"];

    $stmt = $pdo->prepare("SELECT * FROM felhasznalok WHERE felhasznalonev = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["jelszo"])) {

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["felhasznalonev"];

        header("Location: dashboard.php");
        exit;

    } else {
        echo "Hibás belépési adatok!";
    }
}
?>

<form method="POST">
    <input type="text" name="felhasznalonev" placeholder="Felhasználónév" required>
    <input type="password" name="jelszo" placeholder="Jelszó" required>
    <button type="submit">Bejelentkezés</button>
</form>