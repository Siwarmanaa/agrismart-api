<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/config.php';

$email = trim($_GET['email'] ?? '');
if (!$email) {
    http_response_code(400);
    echo json_encode([ 'success' => false, 'message' => 'Email requis', 'cultures' => [] ]);
    exit;
}

$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo json_encode([ 'success' => true, 'cultures' => [] ]);
    $stmt->close();
    $conn->close();
    exit;
}

$user_id = (int)$row['id'];
$stmt->close();

$stmt2 = $conn->prepare('SELECT cultures FROM cultures2 WHERE user_id = ?');
$stmt2->bind_param('i', $user_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
if ($r = $res2->fetch_assoc()) {
    $json = $r['cultures'];
    $arr = json_decode($json, true);
    if (!is_array($arr)) { $arr = []; }
    echo json_encode([ 'success' => true, 'cultures' => $arr ]);
} else {
    echo json_encode([ 'success' => true, 'cultures' => [] ]);
}

$stmt2->close();
$conn->close();
