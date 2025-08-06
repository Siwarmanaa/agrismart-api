<?php
session_start();
header("Content-Type: application/json");
require_once "config.php";

$email = $_POST['email'] ?? '';
$pass = $_POST['motdepasse'] ?? '';

if (!$email || !$pass) {
    echo json_encode(["success" => false, "message" => "Email ou mot de passe manquant"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($pass, $user['motdepasse'])) {
    $_SESSION['user_id'] = $user['id'];
    echo json_encode(["success" => true, "user" => ["id" => $user['id'], "nom" => $user['nom'], "email" => $user['email']]]);
} else {
    echo json_encode(["success" => false, "message" => "Identifiants incorrects"]);
}
