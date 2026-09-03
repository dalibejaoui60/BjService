<?php
session_start();
require "config.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom       = trim($_POST["nom"] ?? "");
    $prenom    = trim($_POST["prenom"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $password  = $_POST["password"] ?? "";
    $telephone = trim($_POST["telephone"] ?? "");
    $address   = trim($_POST["address"] ?? "");
    $dateN     = trim($_POST["dateN"] ?? "");
    $sexe      = $_POST["sexe"] ?? "";
    $photo     = "default.png"; // pas d'upload pour l'instant

    if ($nom === "" || $prenom === "" || $email === "" || $password === "" || $dateN === "") {
        $errors[] = "Veuillez remplir tous les champs obligatoires.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    if ($sexe === "" || $sexe === "Choose...") {
        $errors[] = "Veuillez choisir un sexe.";
    }

    if (empty($errors)) {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT idU FROM utilisateur WHERE emailU = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Cet email est déjà utilisé.";
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "INSERT INTO utilisateur (nomU, prenomU, emailU, mdpU, telU, adrU, dateNU, photoU, sexeU)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nom, $prenom, $email, $hashedPassword, $telephone, $address, $dateN, $photo, $sexe]);

        unset($_SESSION["admin_id"]); // évite qu'une session admin restante fausse expediteur/droits

        $_SESSION["user_id"]     = $pdo->lastInsertId();
        $_SESSION["user_prenom"] = $prenom;
        $_SESSION["user_nom"]    = $nom;

        header("Location: index.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inscription — BJ SERVICE</title>
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
    <section><br><br>
      <div class="container">

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul style="margin-bottom:0">
              <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="register.php">
          <div class="row">
            <div class="col-7">
              <label for="nom" class="form-label">Nom</label>
              <input type="text" class="form-control" id="nom" name="nom"
                value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
            </div>
            <div class="col-7">
              <label for="prenom" class="form-label">Prenom</label>
              <input type="text" class="form-control" id="prenom" name="prenom"
                value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
            </div>
            <div class="col-7">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="col-7">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="col-7">
              <label for="telephone" class="form-label">telephone</label>
              <input type="number" class="form-control" id="telephone" name="telephone"
                value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
            </div>
            <div class="col-7">
              <label for="address" class="form-label">Address</label>
              <input type="text" class="form-control" id="address" name="address" placeholder="1234 Main TN"
                value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>
            <div class="col-7">
              <label for="dateN" class="form-label">Date de naissance</label>
              <input type="date" class="form-control" id="dateN" name="dateN"
                value="<?= htmlspecialchars($_POST['dateN'] ?? '') ?>">
            </div>
            <div class="col-7">
              <label for="sexe" class="form-label">Sexe</label>
              <select id="sexe" name="sexe" class="form-select">
                <option selected disabled>Choose...</option>
                <option>Homme</option>
                <option>Femme</option>
              </select>
              <br>
            </div>

            <div class="col-12">
              <button type="submit" class="btn btn-primary">S'inscrire</button>
              <button type="reset" class="btn btn-primary">Annuler</button>
              <br><br><br>
            </div>
          </div>
        </form>
      </div>
    </section>
  </main>
  <?php include "footer.php"; ?>

  <script src="assets/js/main.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
