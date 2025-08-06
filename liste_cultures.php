<?php
session_start();
header("Content-Type: application/json");
require_once "config.php"; // doit définir $conn comme instance de PDO

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Non authentifié"]);
    exit;
}

$users_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM cultures WHERE users_id = ?");
$stmt->execute([$users_id]);
$cultures = $stmt->fetchAll(PDO::FETCH_ASSOC); // <- ici, fetchAll sur le statement

echo json_encode(["success" => true, "cultures" => $cultures]);
