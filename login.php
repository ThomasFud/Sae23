<?php
// ============================================================
//  Login page: one form, detects the role (admin or manager)
// ============================================================
require 'db.php';
require 'auth.php';
$title = "Connexion";
$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = mysqli_real_escape_string($db, $_POST['login']);
    $hash  = md5($_POST['motdepasse']);   // passwords are stored as MD5

    // 1) Try the administrator account
    $r = mysqli_query($db, "SELECT id_admin FROM Administrateur
                            WHERE login='$login' AND mot_de_passe='$hash'");
    if (mysqli_num_rows($r) === 1) {
        $_SESSION['role']  = 'admin';
        $_SESSION['login'] = $_POST['login'];
        header("Location: admin.php");
        exit;
    }

    // 2) Otherwise try the manager accounts
    $r = mysqli_query($db, "SELECT id_gestionnaire FROM Gestionnaire
                            WHERE login='$login' AND mot_de_passe='$hash'");
    if (mysqli_num_rows($r) === 1) {
        $row = mysqli_fetch_assoc($r);
        $_SESSION['role'] = 'gestionnaire';
        $_SESSION['login'] = $_POST['login'];
        $_SESSION['id_gestionnaire'] = $row['id_gestionnaire'];
        header("Location: gestion.php");
        exit;
    }

    $erreur = "Login ou mot de passe incorrect.";
}

include 'header.php';
?>
<div class="login-box">
  <h1>Connexion</h1>
  <?php if ($erreur): ?><div class="error"><?php echo htmlspecialchars($erreur); ?></div><?php endif; ?>
  <form method="post">
    <input type="text" name="login" placeholder="Login" required>
    <input type="password" name="motdepasse" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
  </form>
</div>
<?php include 'footer.php'; ?>
