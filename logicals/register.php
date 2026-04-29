<?php
require_once "../config/database.php";

$uzenet = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["felhasznalonev"]);
    $email = trim($_POST["email"]);
    $fullname = trim($_POST["teljnev"]);
    $password = $_POST["jelszo"];
    $password2 = $_POST["jelszo_ujra"];

    try {
        // 1. Jelszavak egyezésének ellenőrzése
        if ($password !== $password2) {
            $uzenet = "A két jelszó nem egyezik meg.";
        } 
        else {
            // 2. Felhasználónév ellenőrzése
            $check = $pdo->prepare("SELECT id FROM felhasznalok WHERE felhasznalonev = ?");
            $check->execute([$username]);
            
            if ($check->fetch()) {
                $uzenet = "Ez a felhasználónév már foglalt!";
            } 
            else {
                // 3. Email cím ellenőrzése
                $check2 = $pdo->prepare("SELECT id FROM felhasznalok WHERE email = ?");
                $check2->execute([$email]);

                if ($check2->fetch()) {
                    $uzenet = "Ez az email cím már foglalt!";
                } 
                else {
                    // 4. Regisztráció
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO felhasznalok (felhasznalonev, jelszo, teljnev, email)
                        VALUES (?, ?, ?, ?)
                    ");

                    $stmt->execute([$username, $passwordHash, $fullname, $email]);
                    $uzenet = "Sikeres regisztráció!";
                }
            }
        }

    } catch (PDOException $e) {
        $uzenet = "Hiba történt a mentés során.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
</head>
<body>

<h2>Regisztráció</h2>

<form method="POST">
    <input type="text" name="felhasznalonev" placeholder="Felhasználónév" required><br><br>
    <input type="text" name="email" placeholder="Email" required><br><br>
	<input type="text" name="teljnev" placeholder="Teljes név" required><br><br>
    <input type="password" name="jelszo" placeholder="Jelszó" required><br><br>
    <input type="password" name="jelszo_ujra" placeholder="Jelszó újra" required><br><br>
    <button type="submit">Regisztráció</button>
</form>

<p><?php echo $uzenet; ?></p>

</body>
</html>