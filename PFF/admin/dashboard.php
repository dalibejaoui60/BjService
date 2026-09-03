<?php
session_start();
require "../config.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin.php");
    exit;
}

// Stats
$nbEnAttente = $pdo->query("SELECT COUNT(*) FROM panier WHERE statutPa = 'en attente'")->fetchColumn();
$nbTermine   = $pdo->query("SELECT COUNT(*) FROM panier WHERE statutPa = 'terminé'")->fetchColumn();
$nbReclam    = $pdo->query("SELECT COUNT(*) FROM reclamation WHERE statut = 'nouvelle'")->fetchColumn();
$nbClients   = $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
$revenu      = $pdo->query("
    SELECT COALESCE(SUM(COALESCE(p.prixFinal, s.prixS)), 0)
    FROM panier p
    JOIN regrouper r ON r.idPa = p.idPa
    JOIN service s ON s.idS = r.idS
    WHERE p.statutPa = 'terminé'
")->fetchColumn();

// Dernières commandes
$commandes = $pdo->query("
    SELECT p.idPa, p.statutPa, p.dateCPa, u.prenomU, u.nomU, s.nomS
    FROM panier p
    JOIN utilisateur u ON u.idU = p.idU
    LEFT JOIN regrouper r ON r.idPa = p.idPa
    LEFT JOIN service s ON s.idS = r.idS
    ORDER BY p.idPa DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// Dernières réclamations
$reclamations = $pdo->query("
    SELECT idR, nomClient, sujet, statut, dateR
    FROM reclamation
    ORDER BY idR DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

$currentPage = "dashboard";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard — Admin BJ SERVICE</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <div class="admin-shell">
    <?php include "sidebar.php"; ?>

    <main class="admin-main">
      <div class="admin-topbar">
        <h1>Dashboard</h1>
        <div class="who">Connecté en tant qu'admin</div>
      </div>

      <div class="stat-grid">
        <div class="stat-card indigo">
          <div class="label">Revenu (commandes terminées)</div>
          <div class="num">$<?= number_format($revenu, 0) ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Commandes en attente</div>
          <div class="num"><?= $nbEnAttente ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Réclamations nouvelles</div>
          <div class="num"><?= $nbReclam ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Clients inscrits</div>
          <div class="num"><?= $nbClients ?></div>
        </div>
      </div>

      <div class="admin-panel">
        <h3>Dernières commandes</h3>
        <table class="admin-table">
          <thead><tr><th>#</th><th>Client</th><th>Service</th><th>Statut</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($commandes as $c): ?>
              <tr>
                <td>#<?= $c['idPa'] ?></td>
                <td><?= htmlspecialchars($c['prenomU'] . ' ' . $c['nomU']) ?></td>
                <td><?= htmlspecialchars($c['nomS'] ?? '—') ?></td>
                <td><?= htmlspecialchars($c['statutPa']) ?></td>
                <td><?= htmlspecialchars($c['dateCPa']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($commandes)): ?><div class="admin-empty">Aucune commande pour l'instant.</div><?php endif; ?>
      </div>

      <div class="admin-panel">
        <h3>Dernières réclamations</h3>
        <table class="admin-table">
          <thead><tr><th>#</th><th>Client</th><th>Sujet</th><th>Statut</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($reclamations as $r): ?>
              <tr>
                <td>#<?= $r['idR'] ?></td>
                <td><?= htmlspecialchars($r['nomClient']) ?></td>
                <td><?= htmlspecialchars($r['sujet']) ?></td>
                <td><?= htmlspecialchars($r['statut']) ?></td>
                <td><?= htmlspecialchars($r['dateR']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($reclamations)): ?><div class="admin-empty">Aucune réclamation pour l'instant.</div><?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
