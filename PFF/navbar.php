<?php
// navbar.php — à inclure sur chaque page client (après session_start()+config.php).
?>
  <a class="skip-link" href="#main">Skip to content</a>

  <!-- Header -->
  <header class="site-header">
    <div class="container">

      <a href="index.php" class="brand">
        <span class="brand-mark"><img src="img/logo.png" alt="" ></span>
        BJ SERVICE
      </a>

      <form class="search" role="search" onsubmit="event.preventDefault();">
        <input type="text" placeholder="Rechercher des produits, des marques, des catégories…" aria-label="Search the store" />
        <button type="submit" aria-label="Search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        </button>
      </form>

      <div class="icon-row">
        <?php include "account_widget.php"; ?>
        <a href="chat.php" class="icon-btn icon-btn--cart" aria-label="Messages">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            <circle cx="8" cy="12" r="1" fill="currentColor" stroke="none"/>
            <circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/>
            <circle cx="16" cy="12" r="1" fill="currentColor" stroke="none"/>
          </svg>
        </a>
        <button class="nav-toggle" aria-label="Open menu" aria-expanded="false">≡</button>
      </div>
    </div>
  </header>

  <!-- navbar -->
  <nav class="nav-bar" aria-label="Primary">
    <div class="container">
      <div class="brand-row">
        <a href="index.php" class="brand-logo" style="color:#24A4DD; font-size: 30px;">Accueil</a>
        <a href="index.php#ser1" class="brand-logo">JEUX</a>
        <a href="index.php#ser2" class="brand-logo">PAIEMENT</a>
        <a href="index.php#ser3" class="brand-logo">LOGO</a>
        <a href="index.php#ser4" class="brand-logo">PUBLICITES</a>
        <a href="contact.php" class="brand-logo">contact</a>
      </div>
    </div>
  </nav>

  <!-- Mobile drawer -->
  <div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-head">
      <a href="index.php" class="brand"><span class="brand-mark"><img src="img/logo.png" alt=""></span> BJ SERVICE</a>
      <button class="drawer-close" aria-label="Close menu">Close ✕</button>
    </div>
    <a href="index.php">Accueil</a>
    <a href="index.php#ser1">JEUX</a>
    <a href="index.php#ser2">PAIEMENT</a>
    <a href="index.php#ser3">LOGO</a>
    <a href="index.php#ser4">PUBLICITES</a>
    <a href="contact.php">Contact</a>
    <a href="chat.php" class="btn btn--indigo" style="margin-top:var(--s5); justify-content:center">Mes messages →</a>
  </div>
