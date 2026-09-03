<?php
session_start();
require "config.php";

$wasAdmin = isset($_SESSION["admin_id"]) || (($_GET["as"] ?? "") === "admin");

// Effacer le remember token en base + le cookie
if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("UPDATE utilisateur SET remember_token = NULL, remember_expires = NULL WHERE idU = ?");
    $stmt->execute([$_SESSION["user_id"]]);
}
setcookie("remember_token", "", time() - 3600, "/");

session_destroy();
header("Location: " . ($wasAdmin ? "admin/admin.php" : "login.php"));
exit;
