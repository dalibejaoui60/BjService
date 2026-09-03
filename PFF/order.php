<?php
session_start();
require "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$idU = $_SESSION["user_id"];

// ---- POST : le client a rempli le formulaire -> on crée le panier + chat ----
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idS    = $_POST["service"] ?? null;
    $type   = $_POST["type"] ?? "";
    $texte  = trim($_POST["message"] ?? "");

    if (!$idS || !ctype_digit((string)$idS) || $texte === "") {
        die("Veuillez remplir le message.");
    }

    $stmt = $pdo->prepare("SELECT * FROM service WHERE idS = ?");
    $stmt->execute([$idS]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$service) die("Service introuvable.");

    $stmt = $pdo->prepare("INSERT INTO panier (dateCPa, statutPa, idU) VALUES (CURDATE(), 'en attente', ?)");
    $stmt->execute([$idU]);
    $idPa = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO regrouper (idPa, idS, quantite) VALUES (?, ?, 1)");
    $stmt->execute([$idPa, $idS]);

    $prixTxt = $service["prixS"] > 0 ? " — $" . $service["prixS"] : "";
    $intro = "[" . $type . "] " . $service["nomS"] . $prixTxt . "\n" . $texte;
    $stmt = $pdo->prepare("INSERT INTO messages (idPa, expediteur, contenu) VALUES (?, 'client', ?)");
    $stmt->execute([$idPa, $intro]);

    header("Location: chat.php?panier=" . $idPa);
    exit;
}

// ---- GET : afficher le formulaire "type de demande + message" ----
$idS = $_GET["service"] ?? null;
if (!$idS || !ctype_digit((string)$idS)) {
    die("Service invalide.");
}
$stmt = $pdo->prepare("SELECT * FROM service WHERE idS = ?");
$stmt->execute([$idS]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$service) die("Service introuvable.");
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Chat — BJ SERVICE</title>
  <meta name="theme-color" content="#4F46E5" />
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" />
  <link rel="stylesheet" href="assets/css/styles.css" />
</head>

<body>
  <?php include "navbar.php"; ?><br><br>

  <main id="main">
    <section>
      <div class="container">

        <form class="contact-form" method="post" action="order.php">
          <h2 style="font-size: var(--text-xl); margin-bottom: var(--s2)">Conversation</h2>
          <p style="color:var(--ink-mute); font-size: var(--text-sm); margin-top:-8px">
            Commande : <strong><?= htmlspecialchars($service['nomS']) ?></strong><?= $service['prixS'] > 0 ? ' — $' . htmlspecialchars($service['prixS']) : '' ?>
          </p>

          <input type="hidden" name="service" value="<?= (int)$service['idS'] ?>">

          <div class="field">
            <label for="c-topic">Type de demande?</label>
            <select id="c-topic" name="type">
              <?php
                $types = ["JEUX", "PAIEMENT", "LOGO", "PUBLICITES"];
                foreach ($types as $t) {
                    $sel = ($t === $service['typeS']) ? "selected" : "";
                    echo "<option $sel>$t</option>";
                }
              ?>
            </select>
          </div>

          <div class="field">
            <label for="c-msg">le message</label>
            <textarea id="c-msg" name="message" required placeholder="Décris ta demande ici..."></textarea>
          </div>

          <button class="btn btn--indigo btn--block" type="submit"
            style="padding: 16px; font-size: var(--text-base); margin-top: var(--s2)">Send message →</button>
          <br>
        </form>

      </div>
    </section>
  </main><br><br>
  <?php include "footer.php"; ?>

  <script src="assets/js/main.js" defer></script>
</body>

</html>
