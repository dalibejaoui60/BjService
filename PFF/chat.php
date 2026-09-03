<?php
session_start();
require "config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$idU = $_SESSION["user_id"];

// Liste des conversations (paniers) de l'utilisateur, avec service + dernier message
$stmt = $pdo->prepare("
    SELECT p.idPa, p.statutPa, p.dateCPa,
           s.nomS, s.imageS,
           (SELECT contenu FROM messages m WHERE m.idPa = p.idPa ORDER BY m.idMsg DESC LIMIT 1) AS lastMsg,
           (SELECT dateEnvoi FROM messages m WHERE m.idPa = p.idPa ORDER BY m.idMsg DESC LIMIT 1) AS lastDate
    FROM panier p
    LEFT JOIN regrouper r ON r.idPa = p.idPa
    LEFT JOIN service s ON s.idS = r.idS
    WHERE p.idU = ?
    ORDER BY p.idPa DESC
");
$stmt->execute([$idU]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activePanier = $_GET["panier"] ?? ($conversations[0]["idPa"] ?? null);

// Vérifier que ce panier appartient bien à l'utilisateur
if ($activePanier) {
    $found = false;
    foreach ($conversations as $c) {
        if ($c["idPa"] == $activePanier) $found = true;
    }
    if (!$found) $activePanier = $conversations[0]["idPa"] ?? null;
}

// Statut de la conversation active
$activeStatus = "en attente";
foreach ($conversations as $c) {
    if ($c["idPa"] == $activePanier) { $activeStatus = $c["statutPa"]; break; }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Chat — BJ SERVICE</title>
  <meta name="theme-color" content="#4F46E5" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
    .chat-wrap {
      display: grid;
      grid-template-columns: 300px 1fr;
      height: 70vh;
      min-height: 480px;
      border: 1px solid var(--rule);
      border-radius: 16px;
      overflow: hidden;
      background: var(--paper);
      box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .chat-list {
      border-right: 1px solid var(--rule);
      overflow-y: auto;
      min-height: 0;
      background: var(--bg);
    }
    .chat-list a {
      display: flex;
      gap: 10px;
      align-items: center;
      padding: 12px 14px;
      text-decoration: none;
      color: var(--ink);
      border-bottom: 1px solid var(--rule);
    }
    .chat-list a.active { background: var(--indigo-soft); }
    .chat-list img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; background: var(--bg-soft); }
    .chat-list .meta { min-width: 0; }
    .chat-list .name { font-weight: 600; font-size: var(--text-sm); }
    .chat-list .preview { font-size: var(--text-xs); color: var(--ink-mute); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 190px; }
    .chat-main { display: flex; flex-direction: column; height: 100%; min-height: 0; overflow: hidden; }
    .chat-head { padding: 12px 16px; border-bottom: 1px solid var(--rule); font-weight: 700; display:flex; justify-content: space-between; align-items:center; }
    .chat-timer { font-size: var(--text-xs); color: var(--ink-mute); font-family: var(--ff-mono); }
    .chat-timer.expired { color: var(--rose); }
    .status-badge { display:inline-block; padding: 3px 10px; border-radius:999px; font-size: 11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; }
    .status-badge.pending { background:#FEF3C7; color:#92400E; }
    .status-badge.done { background:#D1FAE5; color:#065F46; }
    .status-badge.cancelled { background:#FEE2E2; color:#991B1B; }
    .chat-body { flex: 1; min-height: 0; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 10px; background: var(--bg); }
    .bubble { max-width: 70%; padding: 10px 14px; border-radius: 14px; font-size: var(--text-sm); line-height: 1.4; }
    .bubble.client { align-self: flex-end; background: var(--indigo); color: #fff; border-bottom-right-radius: 4px; }
    .bubble.admin { align-self: flex-start; background: var(--paper); border: 1px solid var(--rule); border-bottom-left-radius: 4px; }
    .bubble .time { display:block; font-size: 10px; opacity: .7; margin-top: 4px; }
    .chat-input { display: flex; gap: 8px; padding: 12px; border-top: 1px solid var(--rule); }
    .chat-input input { flex: 1; padding: 10px 14px; border-radius: 999px; border: 1px solid var(--rule); font-family: var(--ff-body); }
    .chat-input button { border: none; background: var(--indigo); color: #fff; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; font-size: 16px; }
    .chat-input button:disabled { background: var(--ink-faint); cursor: not-allowed; }
    .chat-empty { display: flex; align-items: center; justify-content: center; height: 100%; color: var(--ink-mute); }
    .locked-banner { background: #FEF2F2; color: var(--rose); font-size: var(--text-xs); text-align: center; padding: 6px; }
    @media (max-width: 700px) {
      .chat-wrap { grid-template-columns: 1fr; grid-template-rows: 130px 1fr; height: 80vh; }
      .chat-list { border-right: none; border-bottom: 1px solid var(--rule); }
      .chat-head { flex-wrap: wrap; gap: 6px; }
    }
  </style>
</head>

<body>
  <?php include "navbar.php"; ?>

  <main id="main">
    <section>
      <div class="container">
        <h2 style="font-size: var(--text-xl); margin: var(--s3) 0">Mes conversations</h2>

        <div class="chat-wrap">
          <div class="chat-list">
            <?php if (empty($conversations)): ?>
              <div style="padding:16px; color:var(--ink-mute); font-size: var(--text-sm)">
                Aucune commande pour l'instant. Clique "Order now" sur un service pour démarrer un chat.
              </div>
            <?php endif; ?>
            <?php foreach ($conversations as $c): ?>
              <a href="chat.php?panier=<?= $c['idPa'] ?>" class="<?= $c['idPa'] == $activePanier ? 'active' : '' ?>">
                <img src="<?= htmlspecialchars($c['imageS'] ?? 'img/logo.png') ?>" alt="">
                <div class="meta">
                  <div class="name"><?= htmlspecialchars($c['nomS'] ?? 'Commande #' . $c['idPa']) ?>
                    <span class="status-badge <?= $c['statutPa'] === 'terminé' ? 'done' : ($c['statutPa'] === 'annulé' ? 'cancelled' : 'pending') ?>" style="margin-left:6px; padding:1px 7px; font-size:9px"><?= $c['statutPa'] === 'terminé' ? 'OK' : ($c['statutPa'] === 'annulé' ? 'X' : '...') ?></span>
                  </div>
                  <div class="preview"><?= htmlspecialchars($c['lastMsg'] ?? '') ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>

          <div class="chat-main">
            <?php if (!$activePanier): ?>
              <div class="chat-empty">Sélectionne une conversation, ou commande un service pour en démarrer une.</div>
            <?php else: ?>
              <div class="chat-head">
                <span class="status-badge <?= $activeStatus === 'terminé' ? 'done' : ($activeStatus === 'annulé' ? 'cancelled' : 'pending') ?>" id="statusBadge">
                  <?= $activeStatus === 'terminé' ? 'Terminé' : ($activeStatus === 'annulé' ? 'Annulé' : 'En attente') ?>
                </span>
                <span class="chat-timer" id="chatTimer">…</span>
              </div>
              <div id="lockedBanner" style="display:none" class="locked-banner">Ce chat est fermé (1h écoulée).</div>
              <div class="chat-body" id="chatBody"></div>
              <div class="chat-input">
                <input type="file" id="imgInput" accept="image/*" style="display:none">
                <button id="attachBtn" type="button" aria-label="Envoyer une image" style="background:transparent; color:var(--ink-mute); border:1px solid var(--rule)">📎</button>
                <input type="text" id="msgInput" placeholder="Say something..." autocomplete="off">
                <button id="sendBtn" aria-label="Envoyer">→</button>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div><br><br>
    </section>
  </main>
  <?php include "footer.php"; ?>

  <script src="assets/js/main.js" defer></script>
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
    let statutActuel = null;

    function addBubble(m) {
      const div = document.createElement('div');
      div.className = 'bubble ' + m.expediteur;
      const time = new Date(m.dateEnvoi.replace(' ', 'T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
      let inner = '';
      if (m.imagePath) {
        inner += '<img src="' + m.imagePath + '" alt="image" style="max-width:200px; border-radius:10px; display:block; margin-bottom:4px; cursor:pointer" onclick="window.open(this.src)">';
      }
      if (m.contenu) inner += escapeHtml(m.contenu);
      div.innerHTML = inner + '<span class="time">' + time + '</span>';
      body.appendChild(div);
    }
    function escapeHtml(s) {
      const d = document.createElement('div');
      d.textContent = s;
      return d.innerHTML;
    }
    function setLocked(locked) {
      input.disabled = locked;
      sendBtn.disabled = locked;
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
        const res = await fetch('fetch_messages.php?panier=' + panierId + '&after=' + lastId);
        const data = await res.json();
        if (data.messages && data.messages.length) {
          data.messages.forEach(m => { addBubble(m); lastId = m.idMsg; });
          body.scrollTop = body.scrollHeight;
        }
        expiresAt = data.expires_at;
        statutActuel = data.statut;
        setLocked(data.locked);
        updateStatusBadge(data.statut);
      } catch (e) { /* silent */ }
    }
    const statusBadge = document.getElementById('statusBadge');
    function updateStatusBadge(statut) {
      if (!statusBadge || !statut) return;
      statusBadge.classList.remove('done', 'cancelled', 'pending');
      if (statut === 'terminé') { statusBadge.classList.add('done'); statusBadge.textContent = 'Terminé'; }
      else if (statut === 'annulé') { statusBadge.classList.add('cancelled'); statusBadge.textContent = 'Annulé'; }
      else { statusBadge.classList.add('pending'); statusBadge.textContent = 'En attente'; }
    }
    async function sendMsg() {
      const val = input.value.trim();
      if (!val) return;
      input.value = '';
      await fetch('send_message.php', {
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
      await fetch('send_message.php', { method: 'POST', body: fd });
      imgInput.value = '';
      attachBtn.disabled = false;
      poll();
    });

    poll();
    setInterval(poll, 3000);
    setInterval(tickTimer, 1000);
  </script>
  <?php endif; ?>
</body>

</html>