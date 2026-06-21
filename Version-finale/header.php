<?php
// Start the session (if not already started) so the navigation
// bar can know whether a user is logged in.
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- Page title: uses the $title set by each page, or "SAE23" by default -->
<title><?php echo isset($title) ? $title : 'SAE23'; ?> - Supervision IUT</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Top navigation bar, shared by every page -->
<header class="topbar">
  <div class="logo">SAE23 &middot; Supervision IUT</div>
  <nav>
    <a href="index.php">Accueil</a>
    <a href="consultation.php">Consultation</a>
    <a href="gestion.php">Gestion</a>
    <a href="admin.php">Administration</a>
    <a href="projet.php">Gestion de projet</a>
    <?php if (isset($_SESSION['role'])): ?>
      <!-- A user is logged in: show their login and a logout link -->
      <a class="auth" href="logout.php">Deconnexion (<?php echo htmlspecialchars($_SESSION['login']); ?>)</a>
    <?php else: ?>
      <!-- Nobody is logged in: show the login link -->
      <a class="auth" href="login.php">Connexion</a>
    <?php endif; ?>
  </nav>
</header>
<!-- Opening of the main container; each page closes it in footer.php -->
<main class="container">
