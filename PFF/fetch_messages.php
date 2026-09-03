<?php
session_start();
require "config.php";
header("Content-Type: application/json");

$idPa = $_GET["panier"] ?? null;
$after = $_GET["after"] ?? 0;

if (!$idPa || !ctype_digit((string)$idPa)) {
    http_response_code(400);
    echo json_encode(["error" => "panier invalide"]);
    exit;
}

// Vérifier les droits : client propriétaire du panier, ou admin connecté
$isAdmin = isset($_SESSION["admin_id"]);
$isOwner = false;

if (!$isAdmin) {
    if (!isset($_SESSION["user_id"])) {
        http_response_code(403);
        echo json_encode(["error" => "non autorisé"]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT idU FROM panier WHERE idPa = ?");
    $stmt->execute([$idPa]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $isOwner = $row && $row["idU"] == $_SESSION["user_id"];
    if (!$isOwner) {
        http_response_code(403);
        echo json_encode(["error" => "non autorisé"]);
        exit;
    }
}

// Nouveaux messages depuis id "after"
$stmt = $pdo->prepare("SELECT idMsg, expediteur, contenu, dateEnvoi, imagePath FROM messages WHERE idPa = ? AND idMsg > ? ORDER BY idMsg ASC");
$stmt->execute([$idPa, $after]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Heure du premier message = début du chat -> +1h = verrouillage automatique (tant que "en attente")
$stmt = $pdo->prepare("SELECT MIN(dateEnvoi) AS debut FROM messages WHERE idPa = ?");
$stmt->execute([$idPa]);
$debut = $stmt->fetch(PDO::FETCH_ASSOC)["debut"];

$timeExpired = false;
$expiresAt = null;
if ($debut) {
    $expiresAt = date("Y-m-d H:i:s", strtotime($debut . " +1 hour"));
    $timeExpired = strtotime($expiresAt) <= time();
}

// Si le délai d'1h est écoulé et que la commande est toujours "en attente" -> Annulé automatiquement
if ($timeExpired) {
    $stmt = $pdo->prepare("UPDATE panier SET statutPa = 'annulé' WHERE idPa = ? AND statutPa = 'en attente'");
    $stmt->execute([$idPa]);
}

$stmt = $pdo->prepare("SELECT statutPa FROM panier WHERE idPa = ?");
$stmt->execute([$idPa]);
$statutPa = $stmt->fetch(PDO::FETCH_ASSOC)["statutPa"] ?? null;

// Le chat se verrouille si le délai est écoulé OU si l'admin a déjà tranché (Terminé / Annulé)
// manuellement, même si l'heure n'est pas encore écoulée.
$locked = $timeExpired || in_array($statutPa, ["terminé", "annulé"], true);

echo json_encode([
    "messages"   => $messages,
    "locked"     => $locked,
    "expires_at" => $expiresAt,
    "statut"     => $statutPa,
]);
