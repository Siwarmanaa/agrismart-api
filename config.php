<?php
// config.php
$servername = "localhost";
$username = "root";    // adapter si besoin
$password = "";
$dbname = "agrismart";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        "success" => false,
        "message" => "Connexion à la base échouée: " . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");
?>