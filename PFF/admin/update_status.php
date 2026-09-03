<?php
session_start();
require "../config.php";
header("Content-Type: application/json");

if (!isset($_SESSION["admin_id"])) {
    http_response_code(403);
    echo json_encode(["error" => "non autorisé"]);
    exit;
}

$idPa   = $_POST["panier"] ?? null;
$statut = $_POST["statut"] ?? null;

$allowed = ["en attente", "terminé", "annulé"];
if (!$idPa || !ctype_digit((string)$idPa) || !in_array($statut, $allowed, true)) {
    http_response_code(400);
    echo json_encode(["error" => "requête invalide"]);
    exit;
}

$stmt = $pdo->prepare("UPDATE panier SET statutPa = ? WHERE idPa = ?");
$stmt->execute([$statut, $idPa]);

echo json_encode(["success" => true]);
