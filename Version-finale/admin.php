<?php
// ============================================================
//  Admin page (protected): buildings (auto-create manager),
//  manager credentials, rooms, sensors - cascade delete
// ============================================================
require 'db.php';
require 'auth.php';
exiger_role('admin');     // only the administrator can open this page
$title = "Administration";
$message = "";            // success message shown to the admin
$erreur  = "";            // error message (kept for consistency)

// All create/delete actions are sent by POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ----- Add a building and auto-create its manager -----
    if ($action === 'add_batiment') {
        $nom = mysqli_real_escape_string($db, $_POST['nom']);
        // Build a default manager login from the building name, e.g. "gest_e".
        $login = "gest_" . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['nom']));
        $login = mysqli_real_escape_string($db, $login);
        // Reuse the manager if it already exists, otherwise create it.
        $r = mysqli_query($db, "SELECT id_gestionnaire FROM Gestionnaire WHERE login='$login'");
        if ($row = mysqli_fetch_assoc($r)) {
            $idg = $row['id_gestionnaire'];
        } else {
            $defpwd = md5("student");   // default password "student" (stored as MD5)
            mysqli_query($db, "INSERT INTO Gestionnaire (login, mot_de_passe, nom, prenom)
                               VALUES ('$login', '$defpwd', '', '')");
            $idg = mysqli_insert_id($db);   // id of the manager just created
        }
        // Link the new building to this manager.
        mysqli_query($db, "INSERT INTO Batiment (nom, id_gestionnaire) VALUES ('$nom', $idg)");
        $message = "Bâtiment ajouté. Gestionnaire « $login » créé (mot de passe par défaut : student). Cliquez sur Identifiants pour le définir.";
    }
    // ----- Edit a manager's login / password -----
    elseif ($action === 'edit_gestionnaire') {
        $id    = intval($_POST['id']);
        $login = mysqli_real_escape_string($db, $_POST['login']);
        $mdp   = $_POST['motdepasse'];
        if ($mdp !== '') {
            // A new password was typed: update both login and password.
            $hash = md5($mdp);
            mysqli_query($db, "UPDATE Gestionnaire SET login='$login', mot_de_passe='$hash' WHERE id_gestionnaire=$id");
        } else {
            // Password left empty: update the login only.
            mysqli_query($db, "UPDATE Gestionnaire SET login='$login' WHERE id_gestionnaire=$id");
        }
        $message = "Identifiants du gestionnaire mis à jour.";
    }
    // ----- Delete a building (cascade: measures, sensors, rooms) -----
    elseif ($action === 'del_batiment') {
        $id = intval($_POST['id']);
        // Remember the manager so we can remove it if it becomes orphan.
        $r = mysqli_query($db, "SELECT id_gestionnaire FROM Batiment WHERE id_batiment=$id");
        $idg = null; if ($row = mysqli_fetch_assoc($r)) $idg = $row['id_gestionnaire'];
        // Delete from the deepest level up: measures, then sensors, then rooms, then building.
        mysqli_query($db, "DELETE FROM Mesure WHERE id_capteur IN (
                             SELECT id_capteur FROM Capteur WHERE id_salle IN (
                               SELECT id_salle FROM Salle WHERE id_batiment=$id))");
        mysqli_query($db, "DELETE FROM Capteur WHERE id_salle IN (
                             SELECT id_salle FROM Salle WHERE id_batiment=$id)");
        mysqli_query($db, "DELETE FROM Salle WHERE id_batiment=$id");
        mysqli_query($db, "DELETE FROM Batiment WHERE id_batiment=$id");
        // If this manager no longer manages any building, delete it too.
        if ($idg !== null) {
            $c = mysqli_query($db, "SELECT COUNT(*) AS n FROM Batiment WHERE id_gestionnaire=$idg");
            $cn = mysqli_fetch_assoc($c);
            if ($cn['n'] == 0) mysqli_query($db, "DELETE FROM Gestionnaire WHERE id_gestionnaire=$idg");
        }
        $message = "Bâtiment supprimé (avec ses salles, capteurs et mesures).";
    }
    // ----- Add a room -----
    elseif ($action === 'add_salle') {
        $nom  = mysqli_real_escape_string($db, $_POST['nom']);
        $type = mysqli_real_escape_string($db, $_POST['type']);
        $cap  = intval($_POST['capacite']);
        $idb  = intval($_POST['id_batiment']);
        mysqli_query($db, "INSERT INTO Salle (nom, type, capacite, id_batiment) VALUES ('$nom','$type',$cap,$idb)");
        $message = "Salle ajoutée.";
    }
    // ----- Delete a room (cascade: measures, sensors) -----
    elseif ($action === 'del_salle') {
        $id = intval($_POST['id']);
        mysqli_query($db, "DELETE FROM Mesure WHERE id_capteur IN (
                             SELECT id_capteur FROM Capteur WHERE id_salle=$id)");
        mysqli_query($db, "DELETE FROM Capteur WHERE id_salle=$id");
        mysqli_query($db, "DELETE FROM Salle WHERE id_salle=$id");
        $message = "Salle supprimée (avec ses capteurs et mesures).";
    }
    // ----- Add a sensor -----
    elseif ($action === 'add_capteur') {
        $nom   = mysqli_real_escape_string($db, $_POST['nom']);
        $type  = mysqli_real_escape_string($db, $_POST['type']);
        $unite = mysqli_real_escape_string($db, $_POST['unite']);
        $ids   = intval($_POST['id_salle']);
        mysqli_query($db, "INSERT INTO Capteur (nom, type, unite, id_salle) VALUES ('$nom','$type','$unite',$ids)");
        $message = "Capteur ajouté.";
    }
    // ----- Delete a sensor (cascade: measures) -----
    elseif ($action === 'del_capteur') {
        $id = intval($_POST['id']);
        mysqli_query($db, "DELETE FROM Mesure WHERE id_capteur=$id");
        mysqli_query($db, "DELETE FROM Capteur WHERE id_capteur=$id");
        $message = "Capteur supprimé (avec ses mesures).";
    }
}

// If the "Identifiants" button was clicked, load the manager to edit
// (its id comes through the URL as ?edit_gest=ID).
$edit_gest = null;
if (isset($_GET['edit_gest'])) {
    $eid = intval($_GET['edit_gest']);
    $r = mysqli_query($db, "SELECT id_gestionnaire, login FROM Gestionnaire WHERE id_gestionnaire=$eid");
    if ($g = mysqli_fetch_assoc($r)) $edit_gest = $g;
}

include 'header.php';
?>
<h1>Administration de la base</h1>
<?php if ($message): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if ($erreur):  ?><div class="error"><?php echo htmlspecialchars($erreur); ?></div><?php endif; ?>

<?php if ($edit_gest): ?>
<!-- Inline form to set the chosen manager's login and password -->
<h2>Identifiants du gestionnaire</h2>
<form method="post" class="form-inline">
  <input type="hidden" name="action" value="edit_gestionnaire">
  <input type="hidden" name="id" value="<?php echo $edit_gest['id_gestionnaire']; ?>">
  <label>Login : <input type="text" name="login" value="<?php echo htmlspecialchars($edit_gest['login']); ?>" required></label>
  <label>Mot de passe : <input type="text" name="motdepasse" placeholder="Nouveau mot de passe"></label>
  <button type="submit">Enregistrer</button>
  <a href="admin.php" style="margin-left:8px;color:#64748b;">Annuler</a>
</form>
<?php endif; ?>

<!-- ================= BUILDINGS ================= -->
<h2>Bâtiments</h2>
<!-- Add-building form -->
<form method="post" class="form-inline">
  <input type="hidden" name="action" value="add_batiment">
  <input type="text" name="nom" placeholder="Nom du bâtiment (ex: E)" required>
  <button type="submit">Ajouter</button>
</form>
<table>
  <tr><th>Nom</th><th>Gestionnaire</th><th>Actions</th></tr>
<?php
// List every building with its manager login.
$rb = mysqli_query($db, "SELECT b.id_batiment, b.nom, b.id_gestionnaire, g.login
                         FROM Batiment b
                         LEFT JOIN Gestionnaire g ON b.id_gestionnaire = g.id_gestionnaire
                         ORDER BY b.nom");
while ($b = mysqli_fetch_assoc($rb)) {
    echo "<tr><td>".htmlspecialchars($b['nom'])."</td><td>".htmlspecialchars($b['login'])."</td><td>";
    // "Identifiants" link -> opens the manager edit form above.
    echo "<a href='admin.php?edit_gest=".$b['id_gestionnaire']."' style='display:inline-block;background:#0ea5e9;color:#fff;padding:7px 12px;border-radius:8px;text-decoration:none;font-size:.85rem;margin-right:6px;'>Identifiants</a>";
    // Delete form, with a JavaScript confirmation before sending.
    echo "<form method='post' onsubmit=\"return confirm('Supprimer ce bâtiment et TOUTES ses salles, capteurs et mesures ?');\" style='display:inline;box-shadow:none;padding:0;background:none;'>";
    echo "<input type='hidden' name='action' value='del_batiment'><input type='hidden' name='id' value='".$b['id_batiment']."'>";
    echo "<button class='del'>Supprimer</button></form>";
    echo "</td></tr>";
}
?>
</table>

<!-- ================= ROOMS ================= -->
<h2>Salles</h2>
<!-- Add-room form (room is attached to an existing building) -->
<form method="post" class="form-inline">
  <input type="hidden" name="action" value="add_salle">
  <input type="text" name="nom" placeholder="Nom de la salle (ex: E105)" required>
  <input type="text" name="type" placeholder="Type (ex: Salle de cours)">
  <input type="number" name="capacite" placeholder="Capacité" min="0">
  <select name="id_batiment" required>
    <?php
    // Building drop-down for the new room.
    $rb2 = mysqli_query($db, "SELECT id_batiment, nom FROM Batiment ORDER BY nom");
    while ($b = mysqli_fetch_assoc($rb2))
        echo "<option value='".$b['id_batiment']."'>".htmlspecialchars($b['nom'])."</option>";
    ?>
  </select>
  <button type="submit">Ajouter</button>
</form>
<table>
  <tr><th>Salle</th><th>Type</th><th>Capacité</th><th>Bâtiment</th><th>Action</th></tr>
<?php
// List every room with its building.
$rs = mysqli_query($db, "SELECT s.id_salle, s.nom, s.type, s.capacite, b.nom AS batiment
                         FROM Salle s
                         LEFT JOIN Batiment b ON s.id_batiment = b.id_batiment
                         ORDER BY b.nom, s.nom");
while ($s = mysqli_fetch_assoc($rs)) {
    echo "<tr><td>".htmlspecialchars($s['nom'])."</td><td>".htmlspecialchars($s['type'])."</td>";
    echo "<td>".htmlspecialchars($s['capacite'])."</td><td>".htmlspecialchars($s['batiment'])."</td>";
    echo "<td><form method='post' onsubmit=\"return confirm('Supprimer cette salle et ses capteurs/mesures ?');\" style='box-shadow:none;padding:0;background:none;'>";
    echo "<input type='hidden' name='action' value='del_salle'><input type='hidden' name='id' value='".$s['id_salle']."'>";
    echo "<button class='del'>Supprimer</button></form></td></tr>";
}
?>
</table>

<!-- ================= SENSORS ================= -->
<h2>Capteurs</h2>
<!-- Add-sensor form (sensor is attached to an existing room) -->
<form method="post" class="form-inline">
  <input type="hidden" name="action" value="add_capteur">
  <input type="text" name="nom" placeholder="Nom du capteur" required>
  <input type="text" name="type" placeholder="Type (ex: Température)">
  <input type="text" name="unite" placeholder="Unité (ex: degC)">
  <select name="id_salle" required>
    <?php
    // Room drop-down for the new sensor.
    $rsa = mysqli_query($db, "SELECT id_salle, nom FROM Salle ORDER BY nom");
    while ($s = mysqli_fetch_assoc($rsa))
        echo "<option value='".$s['id_salle']."'>".htmlspecialchars($s['nom'])."</option>";
    ?>
  </select>
  <button type="submit">Ajouter</button>
</form>
<table>
  <tr><th>Capteur</th><th>Type</th><th>Unité</th><th>Salle</th><th>Action</th></tr>
<?php
// List every sensor with its room.
$rc = mysqli_query($db, "SELECT c.id_capteur, c.nom, c.type, c.unite, s.nom AS salle
                         FROM Capteur c
                         LEFT JOIN Salle s ON c.id_salle = s.id_salle
                         ORDER BY s.nom, c.nom");
while ($c = mysqli_fetch_assoc($rc)) {
    echo "<tr><td>".htmlspecialchars($c['nom'])."</td><td>".htmlspecialchars($c['type'])."</td>";
    echo "<td>".htmlspecialchars($c['unite'])."</td><td>".htmlspecialchars($c['salle'])."</td>";
    echo "<td><form method='post' onsubmit=\"return confirm('Supprimer ce capteur et ses mesures ?');\" style='box-shadow:none;padding:0;background:none;'>";
    echo "<input type='hidden' name='action' value='del_capteur'><input type='hidden' name='id' value='".$c['id_capteur']."'>";
    echo "<button class='del'>Supprimer</button></form></td></tr>";
}
?>
</table>

<?php include 'footer.php'; ?>
