<?php
session_start();
require "../config.php";
header("Content-Type: application/json");

if (!isset($_SESSION["admin_id"])) {
    http_response_code(403);
    echo json_encode(["error" => "non autorisé"]);
    exit;
}

$idPa = $_POST["panier"] ?? null;
$prix = $_POST["prix"] ?? null;

if (!$idPa || !ctype_digit((string)$idPa) || !is_numeric($prix) || $prix < 0) {
    http_response_code(400);
    echo json_encode(["error" => "requête invalide"]);
    exit;
}

$stmt = $pdo->prepare("UPDATE panier SET prixFinal = ? WHERE idPa = ?");
$stmt->execute([$prix, $idPa]);

echo json_encode(["success" => true]);
