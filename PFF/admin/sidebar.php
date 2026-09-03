<?php
// admin_sidebar.php — suppose session_start()+admin auth déjà vérifiés par la page parente.
// $currentPage doit être défini avant l'include (ex: "dashboard", "commandes", "services", "reclamations", "clients", "chat")
$currentPage = $currentPage ?? "";
function navlink($href, $label, $key, $current, $icon) {
    $active = $key === $current ? "active" : "";
    echo "<a href=\"$href\" class=\"$active\">$icon $label</a>";
}
?>
<aside class="admin-sidebar">
  <a href="dashboard.php" class="brand"><img src="../img/logo.png" alt="" class="dot"> BJ SERVICE</a>
  <button type="button" class="admin-menu-toggle" id="adminMenuToggle" aria-label="Ouvrir le menu" aria-expanded="false">≡</button>
  <nav class="admin-nav" id="adminNav">
    <?php navlink("dashboard.php", "Dashboard", "dashboard", $currentPage,
      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>'); ?>
    <?php navlink("commandes.php", "Commandes", "commandes", $currentPage,
      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2l-2 5v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7l-2-5z"/><path d="M4 7h16"/></svg>'); ?>
    <?php navlink("services.php", "Services", "services", $currentPage,
      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18"/></svg>'); ?>
    <?php navlink("reclamations.php", "Réclamations", "reclamations", $currentPage,
      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>'); ?>
    <?php navlink("clients.php", "Clients", "clients", $currentPage,
      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>'); ?>
    <?php navlink("chat.php", "Chat", "chat", $currentPage,
      '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>'); ?>
    <div class="sep"></div>
    <a href="../logout.php?as=admin"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Déconnexion</a>
  </nav>
</aside>

<script>
(function () {
  var btn = document.getElementById("adminMenuToggle");
  var nav = document.getElementById("adminNav");
  if (!btn || !nav) return;

  function setOpen(open) {
    nav.classList.toggle("is-open", open);
    btn.setAttribute("aria-expanded", String(open));
  }

  btn.addEventListener("click", function () {
    setOpen(!nav.classList.contains("is-open"));
  });

  nav.querySelectorAll("a").forEach(function (a) {
    a.addEventListener("click", function () { setOpen(false); });
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") setOpen(false);
  });
})();
</script>
