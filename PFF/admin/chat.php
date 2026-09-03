<?php
session_start();
require "../config.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin.php");
    exit;
}

// Toutes les conversations, avec nom client + service + dernier message
$stmt = $pdo->query("
    SELECT p.idPa, p.statutPa,
           u.nomU, u.prenomU,
           s.nomS,
           (SELECT contenu FROM messages m WHERE m.idPa = p.idPa ORDER BY m.idMsg DESC LIMIT 1) AS lastMsg
    FROM panier p
    JOIN utilisateur u ON u.idU = p.idU
    LEFT JOIN regrouper r ON r.idPa = p.idPa
    LEFT JOIN service s ON s.idS = r.idS
    ORDER BY p.idPa DESC
");
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activePanier = $_GET["panier"] ?? ($conversations[0]["idPa"] ?? null);

$activeStatus = "en attente";
foreach ($conversations as $c) {
    if ($c["idPa"] == $activePanier) { $activeStatus = $c["statutPa"]; break; }
}

$activePrix = null;
if ($activePanier) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(p.prixFinal, s.prixS) AS prix
        FROM panier p
        LEFT JOIN regrouper r ON r.idPa = p.idPa
        LEFT JOIN service s ON s.idS = r.idS
        WHERE p.idPa = ?
    ");
    $stmt->execute([$activePanier]);
    $activePrix = $stmt->fetchColumn();
}
$currentPage = "chat";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Chat — Admin BJ SERVICE</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
  <style>
    html, body.admin-body { height: 100%; overflow: hidden; }
    body.admin-body { background: var(--bg); }
    .admin-shell { height: 100vh; overflow: hidden; }
    .admin-sidebar { overflow-y: auto; }
    .chat-wrap { display: grid; grid-template-columns: 320px 1fr; flex: 1; min-height: 0; border: 1px solid var(--rule); border-radius: 16px; overflow: hidden; background: var(--paper); }
    .chat-list { border-right: 1px solid var(--rule); overflow-y: auto; min-height: 0; }
    .chat-list a { display: block; padding: 12px 14px; text-decoration: none; color: var(--ink); border-bottom: 1px solid var(--rule); }
    .chat-list a.active { background: var(--indigo-soft); }
    .chat-list .name { font-weight: 700; font-size: var(--text-sm); }
    .chat-list .sub { font-size: var(--text-xs); color: var(--ink-mute); }
    .chat-list .preview { font-size: var(--text-xs); color: var(--ink-mute); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .chat-main { display: flex; flex-direction: column; min-height: 0; overflow: hidden; }
    .chat-head { padding: 12px 16px; border-bottom: 1px solid var(--rule); font-weight: 700; display:flex; justify-content:space-between; }
    .chat-timer { font-size: var(--text-xs); color: var(--ink-mute); font-family: var(--ff-mono); }
    .chat-timer.expired { color: var(--rose); }
    .status-badge { display:inline-block; padding: 3px 10px; border-radius:999px; font-size: 11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; }
    .status-badge.pending { background:#FEF3C7; color:#92400E; }
    .status-badge.done { background:#D1FAE5; color:#065F46; }
    .status-badge.cancelled { background:#FEE2E2; color:#991B1B; }
    .status-select { border-radius:999px; border:1px solid var(--rule); padding:4px 10px; font-size:12px; font-weight:600; cursor:pointer; }
    .chat-body { flex:1; min-height: 0; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:10px; background: var(--bg); }
    .bubble { max-width: 70%; padding: 10px 14px; border-radius: 14px; font-size: var(--text-sm); }
    .bubble.admin { align-self: flex-end; background: var(--indigo); color:#fff; border-bottom-right-radius:4px; }
    .bubble.client { align-self: flex-start; background: var(--paper); border:1px solid var(--rule); border-bottom-left-radius:4px; }
    .bubble .time { display:block; font-size:10px; opacity:.7; margin-top:4px; }
    .chat-input { display:flex; gap:8px; padding:12px; border-top:1px solid var(--rule); }
    .chat-input input { flex:1; padding:10px 14px; border-radius:999px; border:1px solid var(--rule); }
    @media (max-width: 900px) {
      .chat-wrap { grid-template-columns: 1fr; grid-template-rows: 140px 1fr; }
      .chat-list { border-right: none; border-bottom: 1px solid var(--rule); }
      .chat-head { flex-wrap: wrap; gap: 8px; }
      .chat-head > div { flex-wrap: wrap; }
    }
    .chat-input button { border:none; background: var(--indigo); color:#fff; width:42px; height:42px; border-radius:50%; }
    .chat-input button:disabled { background: var(--ink-faint); }
    .locked-banner { background:#FEF2F2; color:var(--rose); font-size:var(--text-xs); text-align:center; padding:6px; }
  </style>
</head>
<body class="admin-body">
  <div class="admin-shell">
    <?php include "sidebar.php"; ?>
    <main class="admin-main" style="padding: 20px 24px; display:flex; flex-direction:column; height: 100vh; box-sizing: border-box;">
      <div class="admin-topbar" style="margin-bottom:16px">
        <h1>Chat</h1>
      </div>

  <div class="chat-wrap">
    <div class="chat-list">
      <?php if (empty($conversations)): ?>
        <div style="padding:16px;color:var(--ink-mute)">Aucune conversation pour l'instant.</div>
      <?php endif; ?>
      <?php foreach ($conversations as $c): ?>
        <a href="chat.php?panier=<?= $c['idPa'] ?>" class="<?= $c['idPa'] == $activePanier ? 'active' : '' ?>">
          <div class="name"><?= htmlspecialchars($c['prenomU'] . ' ' . $c['nomU']) ?>
            <span class="status-badge <?= $c['statutPa'] === 'terminé' ? 'done' : ($c['statutPa'] === 'annulé' ? 'cancelled' : 'pending') ?>" style="margin-left:6px; padding:1px 7px; font-size:9px"><?= $c['statutPa'] === 'terminé' ? 'OK' : ($c['statutPa'] === 'annulé' ? 'X' : '...') ?></span>
          </div>
          <div class="sub"><?= htmlspecialchars($c['nomS'] ?? 'Commande #' . $c['idPa']) ?></div>
          <div class="preview"><?= htmlspecialchars($c['lastMsg'] ?? '') ?></div>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="chat-main">
      <?php if (!$activePanier): ?>
        <div style="padding:40px;text-align:center;color:var(--ink-mute)">Sélectionne une conversation.</div>
      <?php else: ?>
        <div class="chat-head">
          <span>Commande #<?= $activePanier ?></span>
          <div style="display:flex; align-items:center; gap:10px">
            <div style="display:flex; align-items:center; gap:4px">
              <span style="font-size:12px; color:var(--ink-mute)">$</span>
              <input type="number" id="prixInput" min="0" step="0.5" value="<?= htmlspecialchars($activePrix ?? 0) ?>"
                style="width:70px; border-radius:8px; border:1px solid var(--rule); padding:4px 6px; font-size:13px">
              <button id="savePrixBtn" class="status-select" style="cursor:pointer">Enregistrer</button>
            </div>
            <select class="status-select" id="statusSelect">
              <option value="en attente" <?= $activeStatus === 'en attente' ? 'selected' : '' ?>>En attente</option>
              <option value="terminé" <?= $activeStatus === 'terminé' ? 'selected' : '' ?>>Terminé</option>
              <option value="annulé" <?= $activeStatus === 'annulé' ? 'selected' : '' ?>>Annulé</option>
            </select>
            <span class="chat-timer" id="chatTimer">…</span>
          </div>
        </div>
        <div id="lockedBanner" style="display:none" class="locked-banner">Ce chat est fermé (1h écoulée).</div>
        <div class="chat-body" id="chatBody"></div>
        <div class="chat-input">
          <input type="file" id="imgInput" accept="image/*" style="display:none">
          <button id="attachBtn" type="button" style="background:transparent; color:var(--ink-mute); border:1px solid var(--rule)">📎</button>
          <input type="text" id="msgInput" placeholder="Répondre au client..." autocomplete="off">
          <button id="sendBtn">→</button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($activePanier): ?>
  <script>
    const panierId = <?= (int)$activePanier ?>;
    let lastId = 0;
    const body = document.getElementById('chatBody');
    const input = document.getElementById('msgInput');
    const sendBtn = document.getElementById('sendBtn');
    const timerEl = document.getElementById('chatTimer');
    const lockedBanner = document.getElementById('lockedBanner');
    let expiresAt = null;
    let statutActuel = "<?= addslashes($activeStatus) ?>";

    function addBubble(m) {
      const div = document.createElement('div');
      div.className = 'bubble ' + m.expediteur;
      const time = new Date(m.dateEnvoi.replace(' ', 'T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
      let inner = '';
      if (m.imagePath) {
        inner += '<img src="../' + m.imagePath + '" alt="image" style="max-width:200px; border-radius:10px; display:block; margin-bottom:4px; cursor:pointer" onclick="window.open(this.src)">';
      }
      if (m.contenu) inner += escapeHtml(m.contenu);
      div.innerHTML = inner + '<span class="time">' + time + '</span>';
      body.appendChild(div);
    }
    function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function setLocked(locked) {
      input.disabled = locked; sendBtn.disabled = locked;
      lockedBanner.style.display = locked ? 'block' : 'none';
      lockedBanner.textContent = (statutActuel === 'terminé') ? 'Commande terminée — chat fermé.'
        : (statutActuel === 'annulé') ? 'Commande annulée — chat fermé.'
        : 'Ce chat est fermé (1h écoulée).';
      timerEl.classList.toggle('expired', locked);
    }
    function tickTimer() {
      if (statutActuel === 'terminé' || statutActuel === 'annulé') { timerEl.textContent = 'Fermé'; return; }
      if (!expiresAt) { timerEl.textContent = ''; return; }
      const diff = new Date(expiresAt.replace(' ', 'T')) - new Date();
      if (diff <= 0) { timerEl.textContent = 'Fermé'; setLocked(true); return; }
      const m = Math.floor(diff / 60000), s = Math.floor((diff % 60000) / 1000);
      timerEl.textContent = 'Ferme dans ' + m + 'm ' + s + 's';
    }
    async function poll() {
      try {
        const res = await fetch('../fetch_messages.php?panier=' + panierId + '&after=' + lastId);
        const data = await res.json();
        if (data.messages && data.messages.length) {
          data.messages.forEach(m => { addBubble(m); lastId = m.idMsg; });
          body.scrollTop = body.scrollHeight;
        }
        expiresAt = data.expires_at;
        statutActuel = data.statut || statutActuel;
        setLocked(data.locked);
      } catch (e) {}
    }
    async function sendMsg() {
      const val = input.value.trim();
      if (!val) return;
      input.value = '';
      await fetch('../send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'panier=' + panierId + '&contenu=' + encodeURIComponent(val)
      });
      poll();
    }
    sendBtn.addEventListener('click', sendMsg);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') sendMsg(); });

    const imgInput = document.getElementById('imgInput');
    const attachBtn = document.getElementById('attachBtn');
    attachBtn.addEventListener('click', () => imgInput.click());
    imgInput.addEventListener('change', async () => {
      if (!imgInput.files.length) return;
      const fd = new FormData();
      fd.append('panier', panierId);
      fd.append('contenu', '');
      fd.append('image', imgInput.files[0]);
      attachBtn.disabled = true;
      await fetch('../send_message.php', { method: 'POST', body: fd });
      imgInput.value = '';
      attachBtn.disabled = false;
      poll();
    });

    const prixInput = document.getElementById('prixInput');
    const savePrixBtn = document.getElementById('savePrixBtn');
    savePrixBtn.addEventListener('click', async () => {
      savePrixBtn.textContent = '...';
      await fetch('update_price.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'panier=' + panierId + '&prix=' + encodeURIComponent(prixInput.value)
      });
      savePrixBtn.textContent = 'Enregistré ✓';
      setTimeout(() => savePrixBtn.textContent = 'Enregistrer', 1500);
    });

    const statusSelect = document.getElementById('statusSelect');
    statusSelect.addEventListener('change', async () => {
      await fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'panier=' + panierId + '&statut=' + encodeURIComponent(statusSelect.value)
      });
      statutActuel = statusSelect.value;
      setLocked(statutActuel === 'terminé' || statutActuel === 'annulé');
      tickTimer();
    });

    poll();
    setInterval(poll, 3000);
    setInterval(tickTimer, 1000);
  </script>
  <?php endif; ?>
    </main>
  </div>
</body>
</html>
