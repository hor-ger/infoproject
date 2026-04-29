<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../logicals/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_user'])) {
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM felhasznalok WHERE id = ?");
        $stmt->execute([$user_id]);

        session_unset();
        session_destroy();

        header("Location: ../index.php");
        exit;

    } catch (PDOException $e) {
        die("Hiba történt a törlés során: " . $e->getMessage());
    }
} else {
    header("Location: ../public/profil.php");
    exit;
}
?>