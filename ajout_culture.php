<?php
session_start();
require_once "config.php";

// Simule que l'utilisateur est connecté.
// Dans une vraie logique, tu stockes user_id à la connexion dans $_SESSION['user_id']
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Non authentifié"]);
    exit;
}

$users_id = $_SESSION['user_id'];
$nom_culture = $_POST['nom_culture'] ?? '';
$type_sol = $_POST['type_sol'] ?? null;
$climat_region = $_POST['climat_region'] ?? null;
$date_plantation = $_POST['date_plantation'] ?? null;

if (!$nom_culture || !$date_plantation) {
    echo json_encode(["success" => false, "message" => "Champs manquants"]);
    exit;
}

// Générer plan de soin basique
$plan_soin = "";
if ($type_sol === "argileux") $plan_soin .= "Irrigation légère. ";
if ($type_sol === "sableux") $plan_soin .= "Arroser plus fréquemment. ";
if ($climat_region === "sec") $plan_soin .= "Prévoir paillage et arrosage régulier. ";

$rappel = date('Y-m-d', strtotime($date_plantation . ' +3 days'));

$stmt = $conn->prepare("
    INSERT INTO cultures 
      (users_id, nom_culture, type_sol, climat_region, date_plantation, plan_soin, rappel)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([$users_id, $nom_culture, $type_sol, $climat_region, $date_plantation, $plan_soin, $rappel]);

echo json_encode([
    "success" => true,
    "message" => "Culture enregistrée",
    "plan_soin" => $plan_soin,
    "rappel" => $rappel
]);
