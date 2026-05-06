<?php
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=koltes_db', 'root', ''
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Adatbázis hiba: " . $e->getMessage());
}