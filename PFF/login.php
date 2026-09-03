<?php
session_start();
require "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE emailU = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["mdpU"])) {
        if (!empty($user["banniU"])) {
            $error = "Ce compte a été suspendu. Contactez le support.";
        } else {
        unset($_SESSION["admin_id"]); // évite qu'une session admin restante fausse expediteur/droits

        $_SESSION["user_id"]     = $user["idU"];
        $_SESSION["user_prenom"] = $user["prenomU"];
        $_SESSION["user_nom"]    = $user["nomU"];

        if (isset($_POST["remember"])) {
            $token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", time() + 30 * 24 * 3600); // 30 jours

            $stmt = $pdo->prepare("UPDATE utilisateur SET remember_token = ?, remember_expires = ? WHERE idU = ?");
            $stmt->execute([hash("sha256", $token), $expires, $user["idU"]]);

            setcookie(
                "remember_token",
                $user["idU"] . ":" . $token,
                time() + 30 * 24 * 3600,
                "/",
                "",
                false, // secure (mets true en HTTPS/prod)
                true   // httponly
            );
        }

        header("Location: index.php");
        exit;
        }
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Connexion — BJ SERVICE</title>
  <meta name="theme-color" content="#4F46E5" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" />
  <link rel="stylesheet" href="assets/css/styles.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
  <?php include "navbar.php"; ?>

  <main id="main">
    <section>
      <div class="container">
        <div class="contact-grid">

          <div class="contact-info">
            <div>
              <div style="position: relative">
                <figure class="figure">
                  <img src="img/lg.png" class="figure-img img-fluid rounded" alt="...">
                </figure>
              </div>
            </div>
          </div>

          <form class="contact-form" method="post" action="login.php">
            <h2 style="font-size: var(--text-xl); margin-bottom: var(--s2)">Connectez-vous</h2>

            <?php if ($error !== ""): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Mot de passe</label>
              <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="remember" name="remember">
              <label class="form-check-label" for="remember">Se souvenir de moi</label>
              <br><br><div class="alert alert-primary" role="alert">
                <a href="register.php" class="alert-link">Creer compte</a>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
            <button type="reset" class="btn btn-primary">Annuler</button>
          </form>

        </div>
      </div>
    </section>
  </main>
  <?php include "footer.php"; ?>

  <script src="assets/js/main.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
