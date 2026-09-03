<?php
// account_widget.php — à inclure dans le header de chaque page.
// Suppose que session_start() a déjà été appelé par la page parente.
if (isset($_SESSION["user_id"])):
?>
  <div class="account-menu" id="accountMenu">
    <button type="button" class="icon-btn account-name" aria-label="Mon compte" onclick="toggleAccountMenu(event)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
      <span><?= htmlspecialchars(($_SESSION["user_prenom"] ?? "") . " " . ($_SESSION["user_nom"] ?? "")) ?></span>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <div class="account-dropdown" id="accountDropdown">
      <a href="logout.php">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Déconnexion
      </a>
    </div>
  </div>
  <script>
    function toggleAccountMenu(e) {
      e.stopPropagation();
      document.getElementById('accountDropdown').classList.toggle('open');
    }
    document.addEventListener('click', function (e) {
      var menu = document.getElementById('accountMenu');
      if (menu && !menu.contains(e.target)) {
        document.getElementById('accountDropdown').classList.remove('open');
      }
    });
  </script>
<?php else: ?>
  <a href="login.php" class="icon-btn" aria-label="Account">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
  </a>
<?php endif; ?>
