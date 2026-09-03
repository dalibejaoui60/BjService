<?php
session_start();
require "../config.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin.php");
    exit;
}

$filtre = $_GET["statut"] ?? "toutes";
$search = trim($_GET["q"] ?? "");

$sql = "
    SELECT p.idPa, p.statutPa, p.dateCPa, u.idU, u.prenomU, u.nomU, s.nomS, s.prixS
    FROM panier p
    JOIN utilisateur u ON u.idU = p.idU
    LEFT JOIN regrouper r ON r.idPa = p.idPa
    LEFT JOIN service s ON s.idS = r.idS
    WHERE 1=1
";
$params = [];

if ($filtre !== "toutes") {
    $sql .= " AND p.statutPa = ?";
    $params[] = $filtre;
}
if ($search !== "") {
    $sql .= " AND (u.prenomU LIKE ? OR u.nomU LIKE ? OR s.nomS LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= " ORDER BY p.idPa DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function badgeClass($s) {
    if ($s === 'terminé') return 'done';
    if ($s === 'annulé') return 'cancelled';
    return 'pending';
}
function badgeLabel($s) {
    if ($s === 'terminé') return 'Terminé';
    if ($s === 'annulé') return 'Annulé';
    return 'En attente';
}

$currentPage = "commandes";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Commandes — Admin BJ SERVICE</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">
  <div class="admin-shell">
    <?php include "sidebar.php"; ?>

    <main class="admin-main">
      <div class="admin-topbar">
        <h1>Commandes</h1>
        <div class="who">Connecté en tant qu'admin</div>
      </div>

      <form method="get" class="admin-filters">
        <a href="?statut=toutes" class="filter-pill <?= $filtre === 'toutes' ? 'active' : '' ?>">Toutes</a>
        <a href="?statut=en+attente" class="filter-pill <?= $filtre === 'en attente' ? 'active' : '' ?>">En attente</a>
        <a href="?statut=terminé" class="filter-pill <?= $filtre === 'terminé' ? 'active' : '' ?>">Terminé</a>
        <a href="?statut=annulé" class="filter-pill <?= $filtre === 'annulé' ? 'active' : '' ?>">Annulé</a>
        <input type="hidden" name="statut" value="<?= htmlspecialchars($filtre) ?>">
        <input type="text" name="q" class="admin-search" placeholder="Rechercher client ou service..."
          value="<?= htmlspecialchars($search) ?>" style="margin-left:auto">
        <button type="submit" class="filter-pill">Rechercher</button>
      </form>

      <div class="admin-panel">
        <table class="admin-table">
          <thead>
            <tr><th>#</th><th>Client</th><th>Service</th><th>Prix</th><th>Statut</th><th>Date</th><th>Chat</th></tr>
          </thead>
          <tbody>
            <?php foreach ($commandes as $c): ?>
              <tr>
                <td>#<?= $c['idPa'] ?></td>
                <td><?= htmlspecialchars($c['prenomU'] . ' ' . $c['nomU']) ?></td>
                <td><?= htmlspecialchars($c['nomS'] ?? '—') ?></td>
                <td><?= $c['prixS'] > 0 ? '$' . htmlspecialchars($c['prixS']) : '—' ?></td>
                <td>
                  <select class="status-select statut-live" data-panier="<?= $c['idPa'] ?>">
                    <option value="en attente" <?= $c['statutPa'] === 'en attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="terminé" <?= $c['statutPa'] === 'terminé' ? 'selected' : '' ?>>Terminé</option>
                    <option value="annulé" <?= $c['statutPa'] === 'annulé' ? 'selected' : '' ?>>Annulé</option>
                  </select>
                </td>
                <td><?= htmlspecialchars($c['dateCPa']) ?></td>
                <td><a href="chat.php?panier=<?= $c['idPa'] ?>" class="filter-pill">Ouvrir</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($commandes)): ?><div class="admin-empty">Aucune commande trouvée.</div><?php endif; ?>
      </div>
    </main>
  </div>

  <script>
    document.querySelectorAll('.statut-live').forEach(function (sel) {
      sel.addEventListener('change', async function () {
        const idPa = this.dataset.panier;
        await fetch('update_status.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'panier=' + idPa + '&statut=' + encodeURIComponent(this.value)
        });
      });
    });
  </script>
</body>
</html>
