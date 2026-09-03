<?php
session_start();
require "../config.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["admin_email_field"] ?? "");
    $pass  = $_POST["admin_pass_field"] ?? "";

    $stmt = $pdo->prepare("SELECT * FROM administrateur WHERE emailA = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($pass, $admin["mdpA"])) {
        unset($_SESSION["user_id"], $_SESSION["user_prenom"], $_SESSION["user_nom"]); // évite qu'une session client restante fausse expediteur/droits

        $_SESSION["admin_id"] = $admin["idA"];
        header("Location: dashboard.php");
        exit;
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
  <title>Admin — BJ SERVICE</title>
  <style>
    * { box-sizing: border-box; }
    html, body {
      height: 100%; margin: 0;
      font-family: 'Segoe UI', system-ui, sans-serif;
    }
    body {
      display: flex; align-items: center; justify-content: center;
      min-height: 100vh;
      background: url('../img/lg.png') center center / cover no-repeat, #05070c;
      position: relative;
    }
    body::before {
      content: "";
      position: absolute; inset: 0;
      background: radial-gradient(circle at center, rgba(5,7,12,0.55) 0%, rgba(5,7,12,0.88) 75%);
      backdrop-filter: blur(2px);
    }
    .login-card {
      position: relative;
      z-index: 1;
      width: 380px;
      max-width: 90vw;
      padding: 40px 34px;
      border-radius: 20px;
      background: rgba(20, 25, 40, 0.45);
      border: 1px solid rgba(255,255,255,0.18);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
      color: #fff;
    }
    .login-card h1 {
      text-align: center;
      font-size: 28px;
      font-weight: 800;
      margin: 0 0 28px;
      letter-spacing: -0.01em;
    }
    .field { position: relative; margin-bottom: 16px; }
    .field input {
      width: 100%;
      padding: 14px 44px 14px 16px;
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,0.25);
      background: rgba(255,255,255,0.08);
      color: #fff;
      font-size: 14px;
      outline: none;
    }
    .field input::placeholder { color: rgba(255,255,255,0.55); }
    .field input:focus { border-color: #6C7BFF; background: rgba(255,255,255,0.14); }
    .field svg {
      position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
      opacity: 0.7; pointer-events: none;
    }
    .row-between {
      display: flex; justify-content: space-between; align-items: center;
      font-size: 13px; margin: 4px 0 22px;
      color: rgba(255,255,255,0.75);
    }
    .row-between label { display: flex; align-items: center; gap: 6px; cursor: pointer; }
    .btn-login {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 999px;
      background: #fff;
      color: #14161F;
      font-weight: 700;
      font-size: 15px;
      cursor: pointer;
    }
    .btn-login:hover { background: #E8E9F5; }
    .error-box {
      background: rgba(220,38,38,0.25);
      border: 1px solid rgba(248,113,113,0.5);
      color: #FCA5A5;
      padding: 10px 14px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 18px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <h1>Admin Login</h1>

    <?php if ($error): ?><div class="error-box"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
      <div class="field">
        <input type="email" name="admin_email_field" placeholder="Email" required autofocus
          autocomplete="off" data-lpignore="true" data-1p-ignore data-bwignore data-form-type="other">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
      </div>
      <div class="field">
        <input type="password" name="admin_pass_field" placeholder="Password" required
          autocomplete="new-password" data-lpignore="true" data-1p-ignore data-bwignore data-form-type="other">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
      </div>

      <div class="row-between">
        <label><input type="checkbox" name="remember"> Remember me</label>
      </div>

      <button type="submit" class="btn-login">Login</button>
    </form>
  </div>
</body>
</html>
