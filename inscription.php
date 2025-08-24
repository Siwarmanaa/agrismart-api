<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/config.php';

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isJson = stripos($contentType, 'application/json') !== false;

if ($isJson) {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $nom = trim($data['nom'] ?? '');
    $email = trim($data['email'] ?? '');
    $motdepasse = $data['motdepasse'] ?? '';
} else {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';
}

if (!$nom || !$email || !$motdepasse) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Tous les champs sont requis."]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Cet email est déjà utilisé."]);
    $stmt->close();
    $conn->close();
    exit;
}

$hash = password_hash($motdepasse, PASSWORD_BCRYPT);
$stmt->close();

$stmt = $conn->prepare("INSERT INTO users (nom, email, motdepasse) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nom, $email, $hash);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Inscription réussie !", "id" => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de l'inscription."]);
}

$stmt->close();
$conn->close();
?>
