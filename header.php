<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo isset($title) ? $title : 'SAE23'; ?> - Supervision IUT</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <div class="logo">SAE23 &middot; Supervision IUT</div>
  <nav>
    <a href="index.php">Accueil</a>
    <a href="consultation.php">Consultation</a>
    <a href="gestion.php">Gestion</a>
    <a href="admin.php">Administration</a>
    <a href="projet.php">Gestion de projet</a>
    <?php if (isset($_SESSION['role'])): ?>
      <a class="auth" href="logout.php">Deconnexion (<?php echo htmlspecialchars($_SESSION['login']); ?>)</a>
    <?php else: ?>
      <a class="auth" href="login.php">Connexion</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
