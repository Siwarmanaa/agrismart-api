<?php
require_once "config.php"; // connexion à la base de données

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // autorise tous les domaines
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// DEBUG : pour vérifier que le serveur reçoit quelque chose
$input = file_get_contents("php://input");
file_put_contents("debug.txt", $input); // ✅ aide à vérifier si JSON reçu

$data = json_decode($input, true);

// Vérification des données
if (!isset($data["email"]) || !isset($data["cultures"])) {
    echo json_encode(["success" => false, "message" => "Données manquantes"]);
    exit;
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$email = trim($data["email"]);
$cultures = $conn->real_escape_string(json_encode($data["cultures"]));

// Vérifier si l'email existe dans la table users
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // L'utilisateur n'existe pas → on l'ajoute
    $stmtInsertUser = $conn->prepare("INSERT INTO users (email) VALUES (?)");
    $stmtInsertUser->bind_param("s", $email);
    $stmtInsertUser->execute();
    $user_id = $stmtInsertUser->insert_id;
} else {
    $row = $result->fetch_assoc();
    $user_id = $row['id'];
}

// Vérifier s'il y a déjà une entrée pour cet utilisateur dans cultures2
$stmt2 = $conn->prepare("SELECT id FROM cultures2 WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows > 0) {
    // Mise à jour
    $stmtUpdate = $conn->prepare("UPDATE cultures2 SET cultures = ? WHERE user_id = ?");
    $stmtUpdate->bind_param("si", $cultures, $user_id);
    $stmtUpdate->execute();
    echo json_encode(["success" => true, "message" => "Cultures mises à jour"]);
} else {
    // Insertion
    $stmtInsert = $conn->prepare("INSERT INTO cultures2 (user_id, cultures) VALUES (?, ?)");
    $stmtInsert->bind_param("is", $user_id, $cultures);
    $stmtInsert->execute();
    echo json_encode(["success" => true, "message" => "Cultures enregistrées"]);
}

$conn->close();
?>
