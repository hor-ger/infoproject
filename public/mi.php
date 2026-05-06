<?php
session_start();
require_once "../config/database.php";

// --- AI CHATBOT LOGIKA ELEJE ---
$botResponse = "";
$operationResult = "";

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Függvény költség hozzáadásához
function addExpenseFromAI($pdo, $user_id, $amount, $category, $note) {
    try {
        $amount = (int) $amount;
        if ($amount <= 0) {
            return "Hibás összeg: az összegnek nagyobbnak kell lennie 0-nál";
        }
        
        // Kategória keresése vagy létrehozása
        $katStmt = $pdo->prepare("SELECT id FROM kategoriak WHERE felhasznalo_id = ? AND nev = ?");
        $katStmt->execute([$user_id, $category]);
        $kat = $katStmt->fetch(PDO::FETCH_ASSOC);

        if ($kat) {
            $kategoria_id = $kat["id"];
        } else {
            $insertKat = $pdo->prepare("INSERT INTO kategoriak (nev, felhasznalo_id) VALUES (?, ?)");
            $insertKat->execute([$category, $user_id]);
            $kategoria_id = $pdo->lastInsertId();
        }

        $datum = date("Y-m-d");
        $insert = $pdo->prepare("INSERT INTO koltesek (osszeg, kategoria_id, felhasznalo_id, megjegyzes, datum) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$amount, $kategoria_id, $user_id, $note, $datum]);

        return "Sikeresen hozzáadva: " . $amount . " Ft a '{$category}' kategóriában. Megjegyzés: " . htmlspecialchars($note);
    } catch (Exception $e) {
        return "Hiba a költség hozzáadásakor: " . $e->getMessage();
    }
}

// Függvény költség törléséhez
function deleteExpenseFromAI($pdo, $user_id, $expense_id) {
    try {
        $expense_id = (int) $expense_id;
        
        // Ellenőrizzük, hogy a költség a felhasználóé
        $checkStmt = $pdo->prepare("SELECT id, osszeg FROM koltesek WHERE id = ? AND felhasznalo_id = ?");
        $checkStmt->execute([$expense_id, $user_id]);
        $expense = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$expense) {
            return "A költség nem található vagy nem a tiéd.";
        }
        
        $deletedAmount = $expense['osszeg'];
        
        // Törlés
        $delete = $pdo->prepare("DELETE FROM koltesek WHERE id = ? AND felhasznalo_id = ?");
        $delete->execute([$expense_id, $user_id]);
        
        return "Sikeresen törölve: " . $deletedAmount . " Ft-os költség.";
    } catch (Exception $e) {
        return "Hiba a költség törlésekor: " . $e->getMessage();
    }
}

// Függvény a felhasználó költéseinek lekéréséhez
function getUserExpenses($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT k2.id, k2.osszeg, COALESCE(k.nev, 'Egyéb') as kategoria, k2.megjegyzes, k2.datum FROM koltesek k2 LEFT JOIN kategoriak k ON k2.kategoria_id = k.id WHERE k2.felhasznalo_id = ? ORDER BY k2.datum DESC LIMIT 20");
        $stmt->execute([$user_id]);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($expenses)) {
            return "Nincsenek költések.";
        }
        
        $result = "A felhasználó költései (legutóbbi 20):\n";
        foreach ($expenses as $exp) {
            $result .= "ID: {$exp['id']}, Összeg: {$exp['osszeg']} Ft, Kategória: {$exp['kategoria']}, Megjegyzés: {$exp['megjegyzes']}, Dátum: {$exp['datum']}\n";
        }
        return $result;
    } catch (Exception $e) {
        return "Hiba a költések lekérésekor: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_message'])) {
    // IDE MÁSOLD BE AZ API KULCSODAT
    $apiKey = "AIzaSyAhwjxIMqu5z0cnhE0TfKXDdapPMusGA4A";
    $userMsg = trim($_POST['user_message']);
    $user_id = $_SESSION["user_id"] ?? null;

    if ($userMsg !== '' && $user_id) {
        $_SESSION['chat_history'][] = [
            'role' => 'user',
            'text' => $userMsg
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;

        // Bővített rendszer utasítás az adatbázissal kapcsolatos tudással
        $userExpenses = getUserExpenses($pdo, $user_id);
        $rendszer_utasitas = "Magyarul beszélj! Te egy professzionális pénzügyi asszisztens vagy ezen a weboldalon. " .
                             "A válaszaid fókuszáljanak a pénzügyekre, a kiadások kezelésére, a megtakarításokra és a tudatos költségvetésre. " .
                             "Ha a kérdés egyáltalán nem kapcsolódik ezekhez, nagyon röviden válaszolj.\n\n" .
                             "FONTOS ADATOK:\n" . $userExpenses . "\n\n" .
                             "Ha a felhasználó azt szeretné, hogy költséget vegyen fel (pl. 'vettem x valamit y forintért' vagy 'adj hozzá egy költséget'), " .
                             "akkor te egy JSON objektumot is generálj a szokásos válaszod után:\n" .
                             "{\"action\": \"add_expense\", \"amount\": SZÁM, \"category\": \"kategória_neve\", \"note\": \"rövid leírás\"}\n\n" .
                             "Ha a felhasználó egy költséget szeretne törölni (pl. 'töröljél ezt' vagy 'id: 123 törlése'), " .
                             "generálj egy DELETE parancsot:\n" .
                             "{\"action\": \"delete_expense\", \"expense_id\": SZÁM}\n\n" .
                             "Elérhető kategóriák: élelmiszer, közlekedés, szórakozás, lakhatás, egészség, egyéb. " .
                             "A JSON-t a válaszod végén helyezd el egy új sorban.";

        $data = [
            "system_instruction" => [
                "parts" => [
                    ["text" => $rendszer_utasitas]
                ]
            ],
            "contents" => [
                [
                    "parts" => [
                        ["text" => $userMsg]
                    ]
                ]
            ]
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'ignore_errors' => true
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            $botResponse = "Hiba történt a kapcsolódás során.";
        } else {
            $responseArray = json_decode($result, true);
            if (isset($responseArray['candidates'][0]['content']['parts'][0]['text'])) {
                $fullResponse = $responseArray['candidates'][0]['content']['parts'][0]['text'];
                
                // JSON parancs keresése és feldolgozása - add_expense vagy delete_expense
                $jsonPattern = '/\{[\s\S]*?"action"\s*:\s*"(add_expense|delete_expense)"[\s\S]*?\}/';
                if (preg_match($jsonPattern, $fullResponse, $matches)) {
                    $jsonCommand = json_decode($matches[0], true);
                    
                    if ($jsonCommand && $jsonCommand['action'] === 'add_expense' && isset($jsonCommand['amount'], $jsonCommand['category'], $jsonCommand['note'])) {
                        $operationResult = addExpenseFromAI($pdo, $user_id, $jsonCommand['amount'], $jsonCommand['category'], $jsonCommand['note']);
                        $botResponse = trim(str_replace($matches[0], "", $fullResponse));
                        if (empty($botResponse)) {
                            $botResponse = "Költség feldolgozva.";
                        }
                    } elseif ($jsonCommand && $jsonCommand['action'] === 'delete_expense' && isset($jsonCommand['expense_id'])) {
                        $operationResult = deleteExpenseFromAI($pdo, $user_id, $jsonCommand['expense_id']);
                        $botResponse = trim(str_replace($matches[0], "", $fullResponse));
                        if (empty($botResponse)) {
                            $botResponse = "Költség feldolgozva.";
                        }
                    } else {
                        $botResponse = $fullResponse;
                    }
                } else {
                    $botResponse = $fullResponse;
                }
            } else {
                // Itt kiolvassuk a Google pontos hibaüzenetét
                $googleHiba = isset($responseArray['error']['message']) ? $responseArray['error']['message'] : 'Ismeretlen hiba formátum. Eredeti válasz: ' . $result;
                $botResponse = "Hiba az API válaszában. A Google szerver ezt üzente: " . $googleHiba;
            }
        }

        // Ha volt adatbázis művelet, azt jelezzük
        if ($operationResult) {
            $botResponse .= "\n\n📊 " . $operationResult;
        }

        $_SESSION['chat_history'][] = [
            'role' => 'bot',
            'text' => $botResponse
        ];
    }
}
// --- AI CHATBOT LOGIKA VÉGE ---
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>AI segítség</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* A chatbot egyedi stílusai, hogy ne akadjanak össze a style.css-el */
        body {
            color: black;
            margin: 0;
            min-height: 100vh;
            background: #2b2b2b;
        }
        .chat-container {
            width: 75vw;
            min-height: 75vh;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            border: 1px solid #000000;
            border-radius: 14px;
            background-color: #007bff;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.14);
        }
        .chat-window {
            height: calc(75vh - 180px);
            overflow-y: auto;
            padding: 18px;
            border: 1px solid #555;
            background: #5d5d5d;
            margin-bottom: 15px;
            border-radius: 10px;
            color: #f5f5f5;
        }
        .chat-window::-webkit-scrollbar {
            width: 10px;
        }
        .chat-window::-webkit-scrollbar-thumb {
            background: #444;
            border-radius: 10px;
        }
        .msg {
            margin-bottom: 15px;
            padding: 14px;
            border-radius: 12px;
            line-height: 1.5;
            color: black;
            max-width: 80%;
        }
        .user-msg {
            background-color: #d1e7dd;
            margin-left: auto;
            border-bottom-right-radius: 0;
        }
        .bot-msg {
            background-color: #e2e3e5;
            margin-right: auto;
            border-bottom-left-radius: 0;
        }
        .chat-form {
            display: flex;
            gap: 10px;
        }
        .chat-form input {
            flex-grow: 1;
            padding: 12px;
            border: 1px solid #999;
            border-radius: 6px;
            font-size: 16px;
            color: black;
            background: #fff;
        }
        .chat-form button {
            padding: 12px 24px;
            background-color: #87b2f3;
            color: black;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .chat-form button:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>

<h1>Bejelentkezett felhasználó: <?= htmlspecialchars($_SESSION["username"] ?? '') ?> </h1>
<nav>
    <ul>
        <li><a href="../index.php">Home</a></li>
        <li><a href="../logicals/dashboard.php">Dashboard</a></li>
        <li><a href="profil.php">Profil</a></li>
        <li><a href="kategoriak.php">Kategóriák</a></li>
        <li><a href="koltesek.php">Költések</a></li>
        <li><a href="legolcsobb.php">Termékek</a></li>
        <li><a href="mi.php">AI segítség</a></li>
        <li><a href="../logicals/logout.php">Kijelentkezés</a></li>
    </ul>
</nav>

<!-- A placeholder lecserélve a működő AI felületre -->
<div class="chat-container">
    <h2>AI Asszisztens</h2>
    
    <div class="chat-window">
        <?php if (!empty($_SESSION['chat_history'])): ?>
            <?php foreach ($_SESSION['chat_history'] as $entry): ?>
                <div class="msg <?= $entry['role'] === 'user' ? 'user-msg' : 'bot-msg' ?>">
                    <strong><?= $entry['role'] === 'user' ? 'Te' : 'AI' ?>:</strong><br>
                    <?= nl2br(htmlspecialchars($entry['text'])) ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #ddd; margin-top: 150px;">Miben segíthetek ma?</p>
        <?php endif; ?>
    </div>

    <form method="POST" class="chat-form" id="messageForm">
        <input type="text" name="user_message" placeholder="Tedd fel a kérdésed..." required autocomplete="off">
        <button type="submit">Küldés</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatWindow = document.querySelector('.chat-window');
    if (chatWindow) {
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }
});

document.getElementById('messageForm').addEventListener('submit', function() {
    setTimeout(function() {
        const chatWindow = document.querySelector('.chat-window');
        if (chatWindow) {
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }
    }, 200);
});
</script>

</body>
</html>