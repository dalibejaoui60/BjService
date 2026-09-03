<?php
session_start();
require "config.php";
header("Content-Type: application/json");

$idPa    = $_POST["panier"] ?? null;
$contenu = trim($_POST["contenu"] ?? "");
$hasImage = isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE;

if (!$idPa || !ctype_digit((string)$idPa) || ($contenu === "" && !$hasImage)) {
    http_response_code(400);
    echo json_encode(["error" => "requête invalide"]);
    exit;
}

$isAdmin = isset($_SESSION["admin_id"]);

if ($isAdmin) {
    $expediteur = "admin";
} elseif (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("SELECT idU FROM panier WHERE idPa = ?");
    $stmt->execute([$idPa]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || $row["idU"] != $_SESSION["user_id"]) {
        http_response_code(403);
        echo json_encode(["error" => "non autorisé"]);
        exit;
    }
    $expediteur = "client";
} else {
    http_response_code(403);
    echo json_encode(["error" => "non autorisé"]);
    exit;
}

// Vérifier que le chat n'est pas verrouillé (1h écoulée)
$stmt = $pdo->prepare("SELECT MIN(dateEnvoi) AS debut FROM messages WHERE idPa = ?");
$stmt->execute([$idPa]);
$debut = $stmt->fetch(PDO::FETCH_ASSOC)["debut"];
if ($debut && strtotime($debut . " +1 hour") <= time()) {
    http_response_code(423);
    echo json_encode(["error" => "chat fermé"]);
    exit;
}

// Gestion de l'upload d'image (optionnel)
$imagePath = null;
if ($hasImage) {
    $file = $_FILES["image"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(["error" => "échec de l'upload"]);
        exit;
    }

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/gif"  => "gif",
        "image/webp" => "webp",
    ];
    $mime = mime_content_type($file["tmp_name"]);
    if (!isset($allowed[$mime]) || $file["size"] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(["error" => "image invalide (jpg/png/gif/webp, max 5 Mo)"]);
        exit;
    }

    $dir = __DIR__ . "/uploads/chat";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = uniqid("msg_", true) . "." . $allowed[$mime];
    if (!move_uploaded_file($file["tmp_name"], $dir . "/" . $filename)) {
        http_response_code(500);
        echo json_encode(["error" => "impossible d'enregistrer l'image"]);
        exit;
    }

    $imagePath = "uploads/chat/" . $filename;
}

$stmt = $pdo->prepare("INSERT INTO messages (idPa, expediteur, contenu, imagePath) VALUES (?, ?, ?, ?)");
$stmt->execute([$idPa, $expediteur, $contenu, $imagePath]);

echo json_encode(["success" => true, "idMsg" => $pdo->lastInsertId()]);
