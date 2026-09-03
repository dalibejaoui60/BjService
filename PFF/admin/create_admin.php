<?php
// ⚠️ À VISITER UNE SEULE FOIS pour créer ton compte admin, puis SUPPRIME ce fichier.
require "../config.php";

$email = "admin@bjservice.com";
$pass  = "admin123"; // change-le après ta première connexion si tu veux

$stmt = $pdo->prepare("SELECT idA FROM administrateur WHERE emailA = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die("Un admin avec cet email existe déjà. Rien à faire.");
}

$hashed = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO administrateur (emailA, mdpA) VALUES (?, ?)");
$stmt->execute([$email, $hashed]);

echo "Admin créé ! Email: $email — Mot de passe: $pass<br>";
echo "Va maintenant sur login.php pour te connecter, puis SUPPRIME ce fichier (create_admin.php).";
