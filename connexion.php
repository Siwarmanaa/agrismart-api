<?php
session_start();
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/config.php';
if ($conn->connect_error) {
    if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        // Si requête JSON => réponse JSON
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Erreur de connexion à la base"]);
    } else {
        die("<p style='color:red;'>Erreur de connexion à la base : " . $conn->connect_error . "</p>");
    }
    exit;
}

// Détecter si POST JSON ou formulaire classique
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    header('Content-Type: application/json; charset=UTF-8');
    // Traitement JSON (React)
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['email']) || empty($data['motdepasse'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Email et mot de passe requis"]);
        exit;
    }

    $email = trim($data['email']);
    $motdepasse = $data['motdepasse'];

    $stmt = $conn->prepare("SELECT id, nom, motdepasse FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($motdepasse, $user['motdepasse'])) {
            $_SESSION['user_id'] = $user['id'];
            echo json_encode([
                "success" => true,
                "user" => [
                    "id" => $user['id'],
                    "nom" => $user['nom'],
                    "email" => $email
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Mot de passe incorrect"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Aucun compte trouvé avec cet email"]);
    }

    $stmt->close();
    $conn->close();

} elseif ($_SERVER["REQUEST_METHOD"] === "POST" && stripos($accept, 'application/json') !== false) {
    header('Content-Type: application/json; charset=UTF-8');
    // POST formulaire mais client attend JSON (React)
    $email = trim($_POST["email"] ?? '');
    $motdepasse = $_POST["motdepasse"] ?? ($_POST['password'] ?? ($_POST['mdp'] ?? ''));

    if (empty($email) || empty($motdepasse)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Email et mot de passe requis"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, nom, motdepasse FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($motdepasse, $user['motdepasse'])) {
            $_SESSION['user_id'] = $user['id'];
            echo json_encode([
                "success" => true,
                "user" => [
                    "id" => $user['id'],
                    "nom" => $user['nom'],
                    "email" => $email
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Mot de passe incorrect"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Aucun compte trouvé avec cet email"]);
    }

    $stmt->close();
    $conn->close();
    exit;

} else {
    // GET: afficher un formulaire HTML simple pour tests manuels
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Connexion</title>
    </head>
    <body>
        <form method="post" action="connexion.php">
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" required>
            <label for="motdepasse">Mot de passe :</label>
            <input type="password" name="motdepasse" id="motdepasse" required>
            <button type="submit">Se connecter</button>
        </form>
    </body>
    </html>
    <?php
}
?>
