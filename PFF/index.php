<?php
session_start();
require "config.php";

$allServices = $pdo->query("SELECT * FROM service ORDER BY idS")->fetchAll(PDO::FETCH_ASSOC);
$servicesByType = ["JEUX" => [], "PAIEMENT" => [], "LOGO" => [], "PUBLICITES" => []];
foreach ($allServices as $s) {
    if (isset($servicesByType[$s["typeS"]])) {
        $servicesByType[$s["typeS"]][] = $s;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sprylo — Tech &amp; Gadgets Ecommerce HTML Template</title>
  <meta name="description" content="Sprylo is a free modern HTML template for tech and electronics stores — multi-color bento hero, indigo primary palette, Plus Jakarta + Outfit + Roboto Mono pair, 5 fully responsive pages, no framework, no build step." />
  <meta name="theme-color" content="#4F46E5" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" />
  <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
  <?php include "navbar.php"; ?>

  <main id="main">

    <!-- HERO: bento grid -->
    <section class="hero">
      <div class="container">

    </section>

    <!-- service 1 -->
    <section class="section" style="padding-top: var(--s5)" id="ser1">
      <div class="container">
        <div class="section-head">
          <h2>JEUX</h2>
        </div>

        <div class="products">
          <?php foreach ($servicesByType["JEUX"] as $s): ?>
            <article class="product-card">
              <div class="img-wrap">
                <img src="<?= htmlspecialchars($s['imageS']) ?>" alt="<?= htmlspecialchars($s['nomS']) ?>" />
              </div>
              <a href="order.php?service=<?= $s['idS'] ?>" class="name"><?= htmlspecialchars($s['nomS']) ?></a>
              <div class="price"><span class="now">$<?= htmlspecialchars($s['prixS']) ?></span></div>
              <a href="order.php?service=<?= $s['idS'] ?>" class="btn">Commandez maintenant →</a>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <!-- service 2 -->
    <section class="section" style="padding-top: var(--s5)" id="ser2">
      <div class="container">
        <div class="section-head">
          <h2>PAIEMENT</h2>
       
        </div>
        <div class="cats-grid">
          <?php foreach ($servicesByType["PAIEMENT"] as $s): ?>
            <a href="order.php?service=<?= $s['idS'] ?>" class="cat-tile">
              <div class="pic"><img src="<?= htmlspecialchars($s['imageS']) ?>" alt="" /></div>
              <div class="name"><?= htmlspecialchars($s['nomS']) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

      <!-- service 3 -->
    <section class="section" style="padding-top: var(--s5)" id="ser3">
      <div class="container">
        <div class="section-head">
          <h2> LOGO </h2>
          
        </div>


        <div class="products">
          <?php foreach ($servicesByType["LOGO"] as $s): ?>
            <article class="product-card">
              <div class="img-wrap">
                <img src="<?= htmlspecialchars($s['imageS']) ?>" alt="<?= htmlspecialchars($s['nomS']) ?>" />
              </div>
              <a href="order.php?service=<?= $s['idS'] ?>" class="name"><?= htmlspecialchars($s['nomS']) ?></a>
              <div class="price"><span class="now">$<?= htmlspecialchars($s['prixS']) ?></span></div>
              <a href="order.php?service=<?= $s['idS'] ?>" class="btn">Commandez maintenant →</a>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

     <!-- service 4 -->
    <section class="section" style="padding-top: var(--s5)" id="ser4">
      <div class="container">
        <div class="section-head">
          <h2>PUBLICITES</h2>
          
        </div>


        <div class="products">
          <?php foreach ($servicesByType["PUBLICITES"] as $s): ?>
            <article class="product-card">
              <div class="img-wrap">
                <img src="<?= htmlspecialchars($s['imageS']) ?>" alt="<?= htmlspecialchars($s['nomS']) ?>" />
              </div>
              <a href="order.php?service=<?= $s['idS'] ?>" class="name"><?= htmlspecialchars($s['nomS']) ?></a>
              <div class="price"><span class="now">$<?= htmlspecialchars($s['prixS']) ?></span></div>
              <a href="order.php?service=<?= $s['idS'] ?>" class="btn">Commandez maintenant →</a>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>


  </main>

  <!-- FOOTER -->
  <?php include "footer.php"; ?>

  <script src="assets/js/main.js" defer></script>
</body>
</html>
