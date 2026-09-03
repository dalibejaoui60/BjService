<?php
session_start();
require "../config.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin.php");
    exit;
}

// ---- Changer le statut (AJAX-like via POST classique) ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "statut") {
    $idR = $_POST["idR"] ?? 0;
    $statut = $_POST["statut"] ?? "";
    if (in_array($statut, ["nouvelle", "en cours", "traitée"], true)) {
        $stmt = $pdo->prepare("UPDATE reclamation SET statut = ? WHERE idR = ?");
        $stmt->execute([$statut, $idR]);
    }
    header("Location: reclamations.php" . (isset($_GET["statut"]) ? "?statut=" . urlencode($_GET["statut"]) : ""));
    exit;
}

$filtre = $_GET["statut"] ?? "toutes";
$sql = "SELECT idR, nomClient, emailClient, sujet, message, statut, dateR, idU, idPa FROM reclamation WHERE 1=1";
$params = [];
if ($filtre !== "toutes") {
    $sql .= " AND statut = ?";
    $params[] = $filtre;
}
$sql .= " ORDER BY idR DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reclamations = $stmt->fetchAll(PDO::FETCH_ASSOC);

function statutClass($s) {
    if ($s === 'traitée') return 'done';
    if ($s === 'en cours') return 'pending';
    return 'cancelled'; // nouvelle -> couleur d'alerte pour attirer l'oeil
}

$currentPage = "reclamations";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Réclamations — Admin BJ SERVICE</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
  <style>
    .rec-list { display: flex; flex-direction: column; gap: 12px; }
    .rec-card { background: var(--paper); border: 1px solid var(--rule); border-radius: var(--r); padding: 16px 18px; box-shadow: var(--shadow-sm); }
    .rec-top { display: flex; justify-content: space-between; align-items: start; gap: 12px; margin-bottom: 8px; flex-wrap: wrap; }
    .rec-who { font-weight: 700; font-size: var(--text-sm); }
    .rec-email { font-size: var(--text-xs); color: var(--ink-mute); }
    .rec-subject { font-weight: 700; font-size: var(--text-base); margin: 6px 0 4px; }
    .rec-message { font-size: var(--text-sm); color: var(--ink-soft); white-space: pre-wrap; }
    .rec-meta { display: flex; justify-content: space-between; align-items: center; margin-top: 12px; flex-wrap: wrap; gap: 10px; }
    .rec-date { font-size: var(--text-xs); color: var(--ink-mute); }
    .rec-actions { display: flex; align-items: center; gap: 8px; }
  </style>
</head>
<body class="admin-body">
  <div class="admin-shell">
    <?php include "sidebar.php"; ?>

    <main class="admin-main">
      <div class="admin-topbar">
        <h1>Réclamations</h1>
        <div class="who">Connecté en tant qu'admin</div>
      </div>

      <div class="admin-filters">
        <a href="?statut=toutes" class="filter-pill <?= $filtre === 'toutes' ? 'active' : '' ?>">Toutes</a>
        <a href="?statut=nouvelle" class="filter-pill <?= $filtre === 'nouvelle' ? 'active' : '' ?>">Nouvelles</a>
        <a href="?statut=en+cours" class="filter-pill <?= $filtre === 'en cours' ? 'active' : '' ?>">En cours</a>
        <a href="?statut=traitée" class="filter-pill <?= $filtre === 'traitée' ? 'active' : '' ?>">Traitées</a>
      </div>

      <?php if (empty($reclamations)): ?>
        <div class="admin-panel"><div class="admin-empty">Aucune réclamation trouvée.</div></div>
      <?php else: ?>
        <div class="rec-list">
          <?php foreach ($reclamations as $r): ?>
            <div class="rec-card">
              <div class="rec-top">
                <div>
                  <div class="rec-who">#<?= $r['idR'] ?> — <?= htmlspecialchars($r['nomClient']) ?></div>
                  <div class="rec-email"><?= htmlspecialchars($r['emailClient']) ?></div>
                </div>
                <span class="status-badge <?= statutClass($r['statut']) ?>"><?= htmlspecialchars(ucfirst($r['statut'])) ?></span>
              </div>
              <div class="rec-subject"><?= htmlspecialchars($r['sujet']) ?></div>
              <div class="rec-message"><?= htmlspecialchars($r['message']) ?></div>
              <div class="rec-meta">
                <span class="rec-date"><?= htmlspecialchars($r['dateR']) ?><?php if ($r['idPa']): ?> · Commande #<?= $r['idPa'] ?><?php endif; ?></span>
                <div class="rec-actions">
                  <?php if ($r['idPa']): ?>
                    <a href="chat.php?panier=<?= $r['idPa'] ?>" class="filter-pill">Voir le chat</a>
                  <?php endif; ?>
                  <?php
                    $gmailTo   = rawurlencode($r['emailClient']);
                    $gmailSub  = rawurlencode("Re: " . $r['sujet'] . " — Réclamation #" . $r['idR']);
                    $gmailBody = rawurlencode(
                      "Bonjour " . $r['nomClient'] . ",\n\n" .
                      "Concernant votre réclamation :\n\"" . $r['message'] . "\"\n\n" .
                      "\n\nCordialement,\nL'équipe BJ SERVICE"
                    );
                    $gmailUrl = "https://mail.google.com/mail/?view=cm&fs=1&to={$gmailTo}&su={$gmailSub}&body={$gmailBody}";
                  ?>
                  <a href="<?= $gmailUrl ?>" target="_blank" rel="noopener" class="filter-pill" style="background:var(--indigo); color:#fff; border-color:var(--indigo)">
                    ✉ Répondre par email
                  </a>
                  <form method="post" style="display:flex; gap:8px; align-items:center">
                    <input type="hidden" name="action" value="statut">
                    <input type="hidden" name="idR" value="<?= $r['idR'] ?>">
                    <select name="statut" class="status-select" onchange="this.form.submit()">
                      <option value="nouvelle" <?= $r['statut'] === 'nouvelle' ? 'selected' : '' ?>>Nouvelle</option>
                      <option value="en cours" <?= $r['statut'] === 'en cours' ? 'selected' : '' ?>>En cours</option>
                      <option value="traitée" <?= $r['statut'] === 'traitée' ? 'selected' : '' ?>>Traitée</option>
                    </select>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
