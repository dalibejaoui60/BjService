<?php
// ============================================
// config.php — Connexion à la base de données
// ============================================
// Réglages par défaut de WAMP : user "root", pas de mot de passe.
// Si ton MySQL a un mot de passe, change $pass ci-dessous.

$host   = "localhost";
$dbname = "bdmf";
$user   = "root";
$pass   = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    // Affiche les erreurs SQL sous forme d'exceptions (plus facile à déboguer)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// ---- Auto-login via cookie "Se souvenir de moi" ----
// (config.php est inclus juste après session_start() sur toutes les pages)
if (!isset($_SESSION["user_id"]) && isset($_COOKIE["remember_token"])) {
    $parts = explode(":", $_COOKIE["remember_token"], 2);
    if (count($parts) === 2) {
        [$cookieIdU, $cookieToken] = $parts;
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE idU = ? AND remember_token = ? AND remember_expires > NOW()");
        $stmt->execute([$cookieIdU, hash("sha256", $cookieToken)]);
        $rememberedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rememberedUser) {
            $_SESSION["user_id"]     = $rememberedUser["idU"];
            $_SESSION["user_prenom"] = $rememberedUser["prenomU"];
            $_SESSION["user_nom"]    = $rememberedUser["nomU"];
        } else {
            // Cookie invalide/expiré -> on le supprime
            setcookie("remember_token", "", time() - 3600, "/");
        }
    }
}
