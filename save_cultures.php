<?php
require_once "config.php"; // connexion à la base de données

header("Content-Type: application/json");

// Récupérer les données JSON envoyées depuis React
$data = json_decode(file_get_contents("php://input"), true);

// Vérification des données
if (!isset($data["email"]) || !isset($data["cultures"])) {
    echo json_encode(["success" => false, "message" => "Données manquantes"]);
    exit;
}
ini_set('display_errors', 1);
error_reporting(E_ALL);//activer les erreurs

$email = trim($data["email"]);
$cultures = json_encode($data["cultures"]); // stocke les cultures en JSON

// Vérifier si l'email existe déjà dans la table users
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Si l’utilisateur n’existe pas, on l’ajoute automatiquement
if ($result->num_rows === 0) {
    $stmtInsertUser = $conn->prepare("INSERT INTO users (email) VALUES (?)");
    $stmtInsertUser->bind_param("s", $email);
    $stmtInsertUser->execute();
    $user_id = $stmtInsertUser->insert_id;
} else {
    $row = $result->fetch_assoc();
    $user_id = $row['id'];
}

// Vérifier si l’email existe déjà dans la table cultures2
$stmt2 = $conn->prepare("SELECT id FROM cultures2 WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows > 0) {
    // Mise à jour si l'utilisateur existe déjà dans cultures2
    $stmtUpdate = $conn->prepare("UPDATE cultures2 SET cultures = ? WHERE user_id = ?");
    $stmtUpdate->bind_param("si", $cultures, $user_id);
    $stmtUpdate->execute();
    echo json_encode(["success" => true, "message" => "Cultures mises à jour"]);
} else {
    // Insertion si l'utilisateur n’existe pas encore dans cultures2
    $stmtInsert = $conn->prepare("INSERT INTO cultures2 (user_id, cultures) VALUES (?, ?)");
    $stmtInsert->bind_param("is", $user_id, $cultures);
    $stmtInsert->execute();
    echo json_encode(["success" => true, "message" => "Cultures enregistrées"]);
}

$conn->close();
?>
