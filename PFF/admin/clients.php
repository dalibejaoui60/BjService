<?php
session_start();
require "../config.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin.php");
    exit;
}

$error = "";
$success = "";

// ---- Ajouter un client ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "add") {
    $nom    = trim($_POST["nomU"] ?? "");
    $prenom = trim($_POST["prenomU"] ?? "");
    $email  = trim($_POST["emailU"] ?? "");
    $pass   = $_POST["mdpU"] ?? "";
    $tel    = trim($_POST["telU"] ?? "");
    $adr    = trim($_POST["adrU"] ?? "");
    $dateN  = $_POST["dateNU"] ?? "";
    $sexe   = $_POST["sexeU"] ?? "";

    if ($nom === "" || $prenom === "" || $email === "" || $pass === "" || $tel === "" || $adr === "" || $dateN === "" || $sexe === "") {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif (strlen($pass) < 6) {
        $error = "Le mot de passe doit faire au moins 6 caractères.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE emailU = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO utilisateur (nomU, prenomU, emailU, mdpU, telU, adrU, dateNU, photoU, sexeU)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'default.png', ?)
            ");
            $stmt->execute([$nom, $prenom, $email, password_hash($pass, PASSWORD_DEFAULT), $tel, $adr, $dateN, $sexe]);
            $success = "Client ajouté.";
        }
    }
}

// ---- Bannir / Débannir ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "toggle_ban") {
    $idU = $_POST["idU"] ?? 0;
    $stmt = $pdo->prepare("UPDATE utilisateur SET banniU = 1 - banniU WHERE idU = ?");
    $stmt->execute([$idU]);
    $success = "Statut mis à jour.";
}

// ---- Supprimer ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete") {
    $idU = $_POST["idU"] ?? 0;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM panier WHERE idU = ?");
    $stmt->execute([$idU]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Impossible de supprimer : ce client a déjà des commandes. Bannis-le plutôt.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE idU = ?");
        $stmt->execute([$idU]);
        $success = "Client supprimé.";
    }
}

$search = trim($_GET["q"] ?? "");

$sql = "
    SELECT u.idU, u.nomU, u.prenomU, u.emailU, u.telU, u.adrU, u.sexeU, u.dateNU, u.banniU,
           COUNT(p.idPa) AS nbCommandes,
           SUM(CASE WHEN p.statutPa = 'terminé' THEN 1 ELSE 0 END) AS nbTerminees
    FROM utilisateur u
    LEFT JOIN panier p ON p.idU = u.idU
    WHERE 1=1
";
$params = [];
if ($search !== "") {
    $sql .= " AND (u.nomU LIKE ? OR u.prenomU LIKE ? OR u.emailU LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= " GROUP BY u.idU ORDER BY u.nomU, u.prenomU";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentPage = "clients";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Clients — Admin BJ SERVICE</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
  <style>
    .client-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--indigo-soft); color: var(--indigo-deep); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; margin-right: 8px; vertical-align: middle; }
    .row-banned td { opacity: .55; }
    .banned-tag { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #991B1B; background: #FEE2E2; padding: 2px 8px; border-radius: 999px; margin-left: 6px; }
    .row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .row-actions button { padding: 6px 12px; border-radius: 8px; border: 1px solid var(--rule); background: var(--paper); font-size: var(--text-xs); font-weight: 600; cursor: pointer; white-space: nowrap; }
    .row-actions button.ban:hover { background: #FEF3C7; border-color: #FCD34D; }
    .row-actions button.unban:hover { background: #D1FAE5; border-color: #6EE7B7; }
    .row-actions button.del:hover { background: #FEE2E2; border-color: #FCA5A5; color: #991B1B; }
    .row-actions button:disabled { opacity: .4; cursor: not-allowed; }
    .alert { padding: 12px 16px; border-radius: 10px; font-size: var(--text-sm); margin-bottom: 16px; }
    .alert.error { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
    .alert.success { background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; }
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); align-items: center; justify-content: center; z-index: 50; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: var(--paper); border-radius: var(--r); padding: 24px; width: 440px; max-width: 92vw; box-shadow: var(--shadow-lg); max-height: 88vh; overflow-y: auto; }
    .modal-box h3 { margin: 0 0 16px; font-family: var(--ff-display); }
    .modal-box .field { margin-bottom: 12px; }
    .modal-box .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .modal-box label { display: block; font-size: var(--text-xs); font-weight: 700; color: var(--ink-mute); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .03em; }
    .modal-box input, .modal-box select {
      width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--rule); font-size: var(--text-sm); font-family: var(--ff-body); outline: none;
    }
    .modal-box input:focus, .modal-box select:focus { border-color: var(--indigo); }
    .modal-actions { display: flex; gap: 10px; margin-top: 18px; }
    .modal-actions .admin-btn { flex: 1; justify-content: center; }
  </style>
</head>
<body class="admin-body">
  <div class="admin-shell">
    <?php include "sidebar.php"; ?>

    <main class="admin-main">
      <div class="admin-topbar">
        <h1>Clients</h1>
        <button class="admin-btn" onclick="document.getElementById('addModal').classList.add('open')">+ Ajouter un client</button>
      </div>

      <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

      <form method="get" class="admin-filters">
        <input type="text" name="q" class="admin-search" placeholder="Rechercher nom, prénom ou email..."
          value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="filter-pill">Rechercher</button>
        <?php if ($search !== ""): ?><a href="clients.php" class="filter-pill">Réinitialiser</a><?php endif; ?>
      </form>

      <div class="admin-panel">
        <table class="admin-table">
          <thead>
            <tr><th>Client</th><th>Contact</th><th>Adresse</th><th>Sexe</th><th>Commandes</th><th>Terminées</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $c): ?>
              <tr class="<?= $c['banniU'] ? 'row-banned' : '' ?>">
                <td>
                  <span class="client-avatar"><?= strtoupper(mb_substr($c['prenomU'], 0, 1) . mb_substr($c['nomU'], 0, 1)) ?></span>
                  <?= htmlspecialchars($c['prenomU'] . ' ' . $c['nomU']) ?>
                  <?php if ($c['banniU']): ?><span class="banned-tag">Banni</span><?php endif; ?>
                </td>
                <td><?= htmlspecialchars($c['emailU']) ?><br><span style="color:var(--ink-mute); font-size: var(--text-xs)"><?= htmlspecialchars($c['telU']) ?></span></td>
                <td><?= htmlspecialchars($c['adrU']) ?></td>
                <td><?= htmlspecialchars($c['sexeU']) ?></td>
                <td><?= (int)$c['nbCommandes'] ?></td>
                <td><?= (int)$c['nbTerminees'] ?></td>
                <td>
                  <div class="row-actions">
                    <form method="post" style="display:inline">
                      <input type="hidden" name="action" value="toggle_ban">
                      <input type="hidden" name="idU" value="<?= $c['idU'] ?>">
                      <button type="submit" class="<?= $c['banniU'] ? 'unban' : 'ban' ?>"
                        onclick="return confirm('<?= $c['banniU'] ? 'Débannir' : 'Bannir' ?> <?= htmlspecialchars(addslashes($c['prenomU'] . ' ' . $c['nomU'])) ?> ?')">
                        <?= $c['banniU'] ? 'Débannir' : 'Bannir' ?>
                      </button>
                    </form>
                    <form method="post" style="display:inline">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="idU" value="<?= $c['idU'] ?>">
                      <button type="submit" class="del" <?= $c['nbCommandes'] > 0 ? 'disabled title="A des commandes — bannis-le plutôt"' : '' ?>
                        onclick="return confirm('Supprimer définitivement <?= htmlspecialchars(addslashes($c['prenomU'] . ' ' . $c['nomU'])) ?> ?')">
                        Supprimer
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($clients)): ?><div class="admin-empty">Aucun client trouvé.</div><?php endif; ?>
      </div>
    </main>
  </div>

  <!-- Modal Ajouter client -->
  <div class="modal-overlay" id="addModal">
    <div class="modal-box">
      <h3>Ajouter un client</h3>
      <form method="post">
        <input type="hidden" name="action" value="add">
        <div class="field-row">
          <div class="field">
            <label>Prénom</label>
            <input type="text" name="prenomU" required>
          </div>
          <div class="field">
            <label>Nom</label>
            <input type="text" name="nomU" required>
          </div>
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" name="emailU" required>
        </div>
        <div class="field">
          <label>Mot de passe</label>
          <input type="password" name="mdpU" minlength="6" required>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Téléphone</label>
            <input type="text" name="telU" required>
          </div>
          <div class="field">
            <label>Sexe</label>
            <select name="sexeU" required>
              <option value="Homme">Homme</option>
              <option value="Femme">Femme</option>
            </select>
          </div>
        </div>
        <div class="field">
          <label>Adresse</label>
          <input type="text" name="adrU" required>
        </div>
        <div class="field">
          <label>Date de naissance</label>
          <input type="date" name="dateNU" required>
        </div>
        <div class="modal-actions">
          <button type="button" class="admin-btn ghost" onclick="document.getElementById('addModal').classList.remove('open')">Annuler</button>
          <button type="submit" class="admin-btn">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
  <script>
    document.getElementById('addModal').addEventListener('click', function (e) {
      if (e.target === this) this.classList.remove('open');
    });
  </script>
</body>
</html>
