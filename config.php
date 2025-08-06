<?php
// config.php
$servername = "localhost";
$username = "root";    // adapter si besoin
$password = "";
$dbname = "agrismart";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Connexion à la base échouée: " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8");
?>