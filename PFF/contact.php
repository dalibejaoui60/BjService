<?php
session_start();
require "config.php";

$success = null; // numéro de réclamation si succès
$errors  = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom     = trim($_POST["nom"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $sujet   = trim($_POST["sujet"] ?? "");
    $message = trim($_POST["message"] ?? "");
    $idPa    = $_POST["commande"] ?? null;
    if ($idPa === "" || $idPa === "0") $idPa = null;

    if ($nom === "" || $email === "" || $message === "") {
        $errors[] = "Veuillez remplir tous les champs obligatoires.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }

    if (empty($errors)) {
        $idU = $_SESSION["user_id"] ?? null;
        $stmt = $pdo->prepare(
            "INSERT INTO reclamation (nomClient, emailClient, sujet, message, idU, idPa) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nom, $email, $sujet, $message, $idU, $idPa]);
        $success = $pdo->lastInsertId();
    }
}

// Commandes du client connecté (pour le dropdown "commande concernée")
$mesCommandes = [];
if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("
        SELECT p.idPa, s.nomS
        FROM panier p
        LEFT JOIN regrouper r ON r.idPa = p.idPa
        LEFT JOIN service s ON s.idS = r.idS
        WHERE p.idU = ?
        ORDER BY p.idPa DESC
    ");
    $stmt->execute([$_SESSION["user_id"]]);
    $mesCommandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Contact — Sprylo</title>
  <meta name="description"
    content="Get in touch with Sprylo customer support — order help, returns, warranty claims, and partnership inquiries." />
  <meta name="theme-color" content="#4F46E5" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .contact-wrap { max-width: 980px; margin: var(--s7) auto; padding: 0 var(--s5); }
    .contact-card {
      border: 1px solid var(--rule);
      border-radius: 24px;
      padding: var(--s6);
      background: var(--paper);
    }
    .contact-card h1 { font-family: var(--ff-display); font-size: var(--text-xl); margin: 0 0 var(--s1); }
    .contact-card .sub { color: var(--fg-soft); font-size: var(--text-sm); margin-bottom: var(--s5); }
    .contact-form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0 var(--s6);
      align-items: start;
    }
    .contact-form-grid .col-msg { display: flex; flex-direction: column; height: 100%; }
    .contact-form-grid .col-msg .field { flex: 1; display: flex; flex-direction: column; }
    .contact-form-grid .col-msg textarea { flex: 1; min-height: 220px; resize: vertical; }
    @media (max-width: 760px) {
      .contact-form-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>

<body>
  <?php include "navbar.php"; ?>

  <main id="main">


    <section>
      <div class="contact-wrap">
        <div class="contact-card">
          <h1>Envoyer une réclamation</h1>
          <p class="sub">Un membre de notre équipe te répondra dès que possible.</p>

          <?php if ($success): ?>
            <div class="alert alert-primary" style="padding:16px; border-radius:10px; background:#EEF2FF; color:#3730A3; margin-bottom:16px">
              ✓ Réclamation envoyée — ton numéro de suivi est <strong>#<?= $success ?></strong>.
            </div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="padding:16px; border-radius:10px; background:#FEF2F2; color:#991B1B; margin-bottom:16px">
              <?php foreach ($errors as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form class="contact-form-grid" method="post" action="contact.php">
            <div class="col-fields">
              <div class="field">
                <label for="c-nom">Nom complet</label>
                <input id="c-nom" name="nom" type="text" required placeholder="Ton nom"
                  value="<?= htmlspecialchars($_POST['nom'] ?? trim(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? ''))) ?>" />
              </div>

              <div class="field">
                <label for="c-email">Email</label>
                <input id="c-email" name="email" type="email" required placeholder="you@example.com"
                  value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
              </div>

              <div class="field">
                <label for="c-topic">Sujet</label>
                <select id="c-topic" name="sujet">
                  <option>JEUX</option>
                  <option>PAIEMENT</option>
                  <option>LOGO</option>
                  <option>PUBLICITES</option>
                  <option>Autre</option>
                </select>
              </div>

              <?php if (!empty($mesCommandes)): ?>
                <div class="field">
                  <label for="c-commande">Commande concernée (optionnel)</label>
                  <select id="c-commande" name="commande">
                    <option value="0">— Aucune commande spécifique —</option>
                    <?php foreach ($mesCommandes as $c): ?>
                      <option value="<?= $c['idPa'] ?>">
                        #<?= $c['idPa'] ?> — <?= htmlspecialchars($c['nomS'] ?? 'Commande') ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php endif; ?>
            </div>

            <div class="col-msg">
              <div class="field">
                <label for="c-msg">Message</label>
                <textarea id="c-msg" name="message" required
                  placeholder="Décris ton problème ici..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
              </div>

              <button class="btn btn--indigo btn--block" type="submit"
                style="padding: 16px; font-size: var(--text-base); margin-top: var(--s2)">Envoyer →</button>
            </div>
          </form>
        </div>
      </div>
    </section>

  </main>
  <?php include "footer.php"; ?>

  <script src="assets/js/main.js" defer></script>
</body>

</html>