<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agrismart";

// Connexion base de données
$conn = new mysqli($servername, $username, $password, $dbname);
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

if ($contentType === "application/json") {
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

} else {
    // Formulaire HTML classique + traitement POST

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $email = trim($_POST["email"] ?? '');
        $motdepasse = $_POST["motdepasse"] ?? '';

        if (empty($email) || empty($motdepasse)) {
            $message = "<p style='color: red;'>Veuillez remplir tous les champs.</p>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "<p style='color: red;'>Email invalide.</p>";
        } else {
            $stmt = $conn->prepare("SELECT id, nom, motdepasse FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $nom, $hashed_password);
                $stmt->fetch();

                if (password_verify($motdepasse, $hashed_password)) {
                    $_SESSION['user_id'] = $id;
                    $message = "<p style='color: green;'>Connexion réussie. Bienvenue, $nom !</p>";
                } else {
                    $message = "<p style='color: red;'>Mot de passe incorrect.</p>";
                }
            } else {
                $message = "<p style='color: red;'>Aucun compte trouvé avec cet email.</p>";
            }
            $stmt->close();
        }
        $conn->close();
    }
    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Connexion</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                padding: 30px;
                background: #f5f5f5;
            }
            .container {
                max-width: 500px;
                margin: auto;
                background: #fff;
                padding: 25px;
                border-radius: 10px;
                box-shadow: 0 0 10px #ccc;
            }
            input, button {
                display: block;
                width: 100%;
                padding: 8px;
                margin-bottom: 15px;
            }
            h4 {
                margin-bottom: 10px;
            }
            p {
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <form method="post" action="connexion.php">
                <h4>Se connecter</h4>
                <hr>
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" required>
                <label for="motdepasse">Mot de passe :</label>
                <input type="password" name="motdepasse" id="motdepasse" required>
                <button type="submit">Se connecter</button>
            </form>
            <?php
            if (isset($message)) echo $message;
            ?>
        </div>
    </body>
    </html>

    <?php
} // fin else formulaire
?>
