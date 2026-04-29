<?php
session_start();
require_once __DIR__ . "/config/database.php";
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Költségkezelés</title>
</head>
<body>

<nav>
    <ul>
        <li><a href="logicals/dashboard.php">Dashboard</a></li>
         <li><a href="public/profil.php">Profil</a></li>
        <li><a href="public/kategoriak.php">Kategóriák</a></li>
        <li><a href="public/koltesek.php">Költések</a></li>
        <li><a href="public/legolcsobb.php">Hol a legolcsóbb?</a></li>
    </ul>
</nav>

<hr>
<h1>Költségkezelés</h1>

<p>Ezen a weboldalon regisztráció után nyomon követheted költéseidet. Elérhető funkciók, és menüpontok:</p>
<ul>
<li><b>Dashboard:</b> Itt tudod nyomonkövetni havi lebontásban a havi költéseidet, azokat megjeleníteni diagramokban, és összehasonlítani például az előző havival</li>
<li><b>Profil:</b>Itt tudod megnézni a profilod adatait, azt törölni, vagy jelszót módosítani</li>
<li><b>Kategóriák:</b>Itt tudsz kategóriát hozzáadni amibe később költségeket tudsz adni, a már meglévő kategóriák látni, és ha szükséges, törölni őket</li>
<li><b>Költések:</b>Itt tudsz felvenni költéseket, megadni milyen célból és mikor történt, milyen kategóriájú, és láthatod az összes eddig felvett költésed</li>
<li><b>Segítség:</b>Mesterséges intelligencia segítségével kérhetsz segítséget költéseid optimalizálására</li>

<li><b>Hol a legolcsóbb?</b>Itt a GVH elérhető adatbázisából kereséssel láthatod főleg élelmiszerek áráról melyik milyen boltban mennyibe kerül</li>

</ul>
</body>
</html>