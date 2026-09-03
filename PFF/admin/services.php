<?php
session_start();
require "../config.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin.php");
    exit;
}

$error = "";
$success = "";

// ---- Ajouter un service ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "add") {
    $nom   = trim($_POST["nomS"] ?? "");
    $prix  = $_POST["prixS"] ?? "";
    $image = trim($_POST["imageS"] ?? "");
    $type  = trim($_POST["typeS"] ?? "");
    $desc  = trim($_POST["descriptionS"] ?? "");

    if ($nom === "" || $prix === "" || $image === "" || $type === "" || $desc === "") {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!is_numeric($prix) || $prix < 0) {
        $error = "Le prix doit être un nombre positif.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO service (nomS, prixS, imageS, typeS, descriptionS) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prix, $image, $type, $desc]);
        $success = "Service ajouté.";
    }
}

// ---- Modifier un service ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "edit") {
    $idS   = $_POST["idS"] ?? 0;
    $nom   = trim($_POST["nomS"] ?? "");
    $prix  = $_POST["prixS"] ?? "";
    $image = trim($_POST["imageS"] ?? "");
    $type  = trim($_POST["typeS"] ?? "");
    $desc  = trim($_POST["descriptionS"] ?? "");

    if ($nom === "" || $prix === "" || $image === "" || $type === "" || $desc === "") {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!is_numeric($prix) || $prix < 0) {
        $error = "Le prix doit être un nombre positif.";
    } else {
        $stmt = $pdo->prepare("UPDATE service SET nomS=?, prixS=?, imageS=?, typeS=?, descriptionS=? WHERE idS=?");
        $stmt->execute([$nom, $prix, $image, $type, $desc, $idS]);
        $success = "Service modifié.";
    }
}

// ---- Supprimer un service ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "delete") {
    $idS = $_POST["idS"] ?? 0;
    // Un service commandé (présent dans regrouper) ne peut pas être supprimé sans casser l'historique des commandes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM regrouper WHERE idS = ?");
    $stmt->execute([$idS]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Impossible de supprimer : ce service a déjà été commandé.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM service WHERE idS = ?");
        $stmt->execute([$idS]);
        $success = "Service supprimé.";
    }
}

$filtre = $_GET["type"] ?? "toutes";
$sql = "SELECT idS, nomS, prixS, imageS, typeS, descriptionS FROM service WHERE 1=1";
$params = [];
if ($filtre !== "toutes") {
    $sql .= " AND typeS = ?";
    $params[] = $filtre;
}
$sql .= " ORDER BY typeS, nomS";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentPage = "services";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Services — Admin BJ SERVICE</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
  <style>
    .svc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
    .svc-card { background: var(--paper); border: 1px solid var(--rule); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; }
    .svc-card img { width: 100%; height: 120px; object-fit: cover; background: var(--bg-soft); }
    .svc-card .body { padding: 14px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
    .svc-card .top { display: flex; justify-content: space-between; align-items: start; gap: 8px; }
    .svc-card .name { font-weight: 700; font-size: var(--text-sm); }
    .svc-card .type-tag { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--indigo); background: var(--indigo-soft); padding: 2px 8px; border-radius: 999px; white-space: nowrap; }
    .svc-card .desc { font-size: var(--text-xs); color: var(--ink-mute); flex: 1; }
    .svc-card .price { font-family: var(--ff-display); font-weight: 800; font-size: var(--text-lg); color: var(--ink); }
    .svc-card .actions { display: flex; gap: 8px; margin-top: 6px; }
    .svc-card .actions button { flex: 1; padding: 8px; border-radius: 8px; border: 1px solid var(--rule); background: var(--paper); font-size: var(--text-xs); font-weight: 600; cursor: pointer; }
    .svc-card .actions button.edit:hover { background: var(--indigo-soft); border-color: var(--indigo-line); }
    .svc-card .actions button.del:hover { background: #FEE2E2; border-color: #FCA5A5; color: #991B1B; }
    .alert { padding: 12px 16px; border-radius: 10px; font-size: var(--text-sm); margin-bottom: 16px; }
    .alert.error { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
    .alert.success { background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; }
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); align-items: center; justify-content: center; z-index: 50; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: var(--paper); border-radius: var(--r); padding: 24px; width: 420px; max-width: 92vw; box-shadow: var(--shadow-lg); }
    .modal-box h3 { margin: 0 0 16px; font-family: var(--ff-display); }
    .modal-box .field { margin-bottom: 12px; }
    .modal-box label { display: block; font-size: var(--text-xs); font-weight: 700; color: var(--ink-mute); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .03em; }
    .modal-box input, .modal-box select, .modal-box textarea {
      width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--rule); font-size: var(--text-sm); font-family: var(--ff-body); outline: none;
    }
    .modal-box input:focus, .modal-box select:focus, .modal-box textarea:focus { border-color: var(--indigo); }
    .modal-actions { display: flex; gap: 10px; margin-top: 18px; }
    .modal-actions .admin-btn { flex: 1; justify-content: center; }
  </style>
</head>
<body class="admin-body">
  <div class="admin-shell">
    <?php include "sidebar.php"; ?>

    <main class="admin-main">
      <div class="admin-topbar">
        <h1>Services</h1>
        <button class="admin-btn" onclick="openAddModal()">+ Ajouter un service</button>
      </div>

      <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

      <div class="admin-filters">
        <a href="?type=toutes" class="filter-pill <?= $filtre === 'toutes' ? 'active' : '' ?>">Toutes</a>
        <a href="?type=JEUX" class="filter-pill <?= $filtre === 'JEUX' ? 'active' : '' ?>">Jeux</a>
        <a href="?type=LOGO" class="filter-pill <?= $filtre === 'LOGO' ? 'active' : '' ?>">Logo</a>
        <a href="?type=PUBLICITES" class="filter-pill <?= $filtre === 'PUBLICITES' ? 'active' : '' ?>">Publicités</a>
        <a href="?type=PAIEMENT" class="filter-pill <?= $filtre === 'PAIEMENT' ? 'active' : '' ?>">Paiement</a>
      </div>

      <?php if (empty($services)): ?>
        <div class="admin-panel"><div class="admin-empty">Aucun service trouvé.</div></div>
      <?php else: ?>
        <div class="svc-grid">
          <?php foreach ($services as $s): ?>
            <div class="svc-card">
              <img src="../<?= htmlspecialchars($s['imageS']) ?>" alt="" onerror="this.src='../img/logo.png'">
              <div class="body">
                <div class="top">
                  <span class="name"><?= htmlspecialchars($s['nomS']) ?></span>
                  <span class="type-tag"><?= htmlspecialchars($s['typeS']) ?></span>
                </div>
                <div class="desc"><?= htmlspecialchars($s['descriptionS']) ?></div>
                <div class="price">$<?= htmlspecialchars($s['prixS']) ?></div>
                <div class="actions">
                  <button type="button" class="edit"
                    onclick='openEditModal(<?= json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Modifier</button>
                  <button type="button" class="del" onclick="confirmDelete(<?= $s['idS'] ?>, '<?= htmlspecialchars(addslashes($s['nomS'])) ?>')">Supprimer</button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>

  <!-- Modal Ajouter / Modifier -->
  <div class="modal-overlay" id="svcModal">
    <div class="modal-box">
      <h3 id="modalTitle">Ajouter un service</h3>
      <form method="post" id="svcForm">
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="idS" id="formIdS" value="">
        <div class="field">
          <label>Nom</label>
          <input type="text" name="nomS" id="formNom" required>
        </div>
        <div class="field">
          <label>Prix ($)</label>
          <input type="number" step="0.01" min="0" name="prixS" id="formPrix" required>
        </div>
        <div class="field">
          <label>Type</label>
          <select name="typeS" id="formType" required>
            <option value="JEUX">JEUX</option>
            <option value="LOGO">LOGO</option>
            <option value="PUBLICITES">PUBLICITES</option>
            <option value="PAIEMENT">PAIEMENT</option>
          </select>
        </div>
        <div class="field">
          <label>Image (chemin relatif, ex: img/valo.png)</label>
          <input type="text" name="imageS" id="formImage" required>
        </div>
        <div class="field">
          <label>Description</label>
          <textarea name="descriptionS" id="formDesc" rows="2" required></textarea>
        </div>
        <div class="modal-actions">
          <button type="button" class="admin-btn ghost" onclick="closeModal()">Annuler</button>
          <button type="submit" class="admin-btn">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Form caché pour la suppression -->
  <form method="post" id="deleteForm" style="display:none">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="idS" id="deleteIdS">
  </form>

  <script>
    const modal = document.getElementById('svcModal');
    function openAddModal() {
      document.getElementById('modalTitle').textContent = 'Ajouter un service';
      document.getElementById('formAction').value = 'add';
      document.getElementById('formIdS').value = '';
      document.getElementById('formNom').value = '';
      document.getElementById('formPrix').value = '';
      document.getElementById('formType').value = 'JEUX';
      document.getElementById('formImage').value = '';
      document.getElementById('formDesc').value = '';
      modal.classList.add('open');
    }
    function openEditModal(s) {
      document.getElementById('modalTitle').textContent = 'Modifier « ' + s.nomS + ' »';
      document.getElementById('formAction').value = 'edit';
      document.getElementById('formIdS').value = s.idS;
      document.getElementById('formNom').value = s.nomS;
      document.getElementById('formPrix').value = s.prixS;
      document.getElementById('formType').value = s.typeS;
      document.getElementById('formImage').value = s.imageS;
      document.getElementById('formDesc').value = s.descriptionS;
      modal.classList.add('open');
    }
    function closeModal() { modal.classList.remove('open'); }
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    function confirmDelete(idS, nom) {
      if (confirm('Supprimer le service « ' + nom + ' » ?')) {
        document.getElementById('deleteIdS').value = idS;
        document.getElementById('deleteForm').submit();
      }
    }
  </script>
</body>
</html>
