<?php
// ============================================================
//  Manager page (protected): measurements of THEIR building,
//  with min / max / average, last 10 measures, and a filter.
// ============================================================
require 'db.php';
require 'auth.php';
exiger_role('gestionnaire');          // managers only

$title   = "Gestion";
$id_gest = intval($_SESSION['id_gestionnaire']);
include 'header.php';

// Buildings managed by this manager
$resBat = mysqli_query($db, "SELECT nom FROM Batiment WHERE id_gestionnaire = $id_gest");
$batNoms = array();
while ($b = mysqli_fetch_assoc($resBat)) { $batNoms[] = $b['nom']; }
?>
<h1>Espace gestionnaire</h1>
<p>Connecté en tant que <strong><?php echo htmlspecialchars($_SESSION['login']); ?></strong>.
Bâtiment(s) géré(s) : <strong><?php echo htmlspecialchars(implode(", ", $batNoms)); ?></strong></p>

<h2>Synthèse des capteurs (min / max / moyenne)</h2>
<table>
  <tr><th>Salle</th><th>Capteur</th><th>Min</th><th>Max</th><th>Moyenne</th><th>Nb mesures</th></tr>
<?php
$sql = "SELECT s.nom AS salle, c.type AS type, c.unite AS unite,
               MIN(m.valeur) AS mini, MAX(m.valeur) AS maxi,
               AVG(m.valeur) AS moy, COUNT(*) AS nb
        FROM Mesure m
        JOIN Capteur c ON m.id_capteur = c.id_capteur
        JOIN Salle s   ON c.id_salle   = s.id_salle
        JOIN Batiment b ON s.id_batiment = b.id_batiment
        WHERE b.id_gestionnaire = $id_gest
        GROUP BY c.id_capteur
        ORDER BY s.nom, c.type";
$res = mysqli_query($db, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    $u = htmlspecialchars($row['unite']);
    echo "<tr>";
    echo "<td>".htmlspecialchars($row['salle'])."</td>";
    echo "<td>".htmlspecialchars($row['type'])."</td>";
    echo "<td>".htmlspecialchars(round($row['mini'],1))." $u</td>";
    echo "<td>".htmlspecialchars(round($row['maxi'],1))." $u</td>";
    echo "<td>".htmlspecialchars(round($row['moy'],1))." $u</td>";
    echo "<td>".htmlspecialchars($row['nb'])."</td>";
    echo "</tr>";
}
?>
</table>

<h2>10 dernières mesures</h2>
<table>
  <tr><th>Salle</th><th>Capteur</th><th>Valeur</th><th>Date</th><th>Heure</th></tr>
<?php
$sql10 = "SELECT s.nom AS salle, c.type AS type, c.unite AS unite,
                 m.valeur, m.date_mesure, m.heure_mesure
          FROM Mesure m
          JOIN Capteur c ON m.id_capteur = c.id_capteur
          JOIN Salle s   ON c.id_salle   = s.id_salle
          JOIN Batiment b ON s.id_batiment = b.id_batiment
          WHERE b.id_gestionnaire = $id_gest
          ORDER BY m.id_mesure DESC
          LIMIT 10";
$res10 = mysqli_query($db, $sql10);
while ($row = mysqli_fetch_assoc($res10)) {
    echo "<tr>";
    echo "<td>".htmlspecialchars($row['salle'])."</td>";
    echo "<td>".htmlspecialchars($row['type'])."</td>";
    echo "<td>".htmlspecialchars($row['valeur'])." ".htmlspecialchars($row['unite'])."</td>";
    echo "<td>".htmlspecialchars($row['date_mesure'])."</td>";
    echo "<td>".htmlspecialchars($row['heure_mesure'])."</td>";
    echo "</tr>";
}
?>
</table>

<h2>Consulter un capteur sur une période</h2>
<form method="get" class="form-inline">
  <label>Capteur :
    <select name="capteur">
    <?php
    $resCap = mysqli_query($db, "SELECT c.id_capteur, s.nom AS salle, c.type
                                 FROM Capteur c
                                 JOIN Salle s ON c.id_salle = s.id_salle
                                 JOIN Batiment b ON s.id_batiment = b.id_batiment
                                 WHERE b.id_gestionnaire = $id_gest
                                 ORDER BY s.nom, c.type");
    while ($c = mysqli_fetch_assoc($resCap)) {
        $sel = (isset($_GET['capteur']) && $_GET['capteur'] == $c['id_capteur']) ? "selected" : "";
        echo "<option value='".$c['id_capteur']."' $sel>".htmlspecialchars($c['salle']." - ".$c['type'])."</option>";
    }
    ?>
    </select>
  </label>
  <label>Du : <input type="date" name="debut" value="<?php echo isset($_GET['debut']) ? htmlspecialchars($_GET['debut']) : date('Y-m-d', strtotime('-7 days')); ?>"></label>
  <label>Au : <input type="date" name="fin" value="<?php echo isset($_GET['fin']) ? htmlspecialchars($_GET['fin']) : date('Y-m-d'); ?>"></label>
  <button type="submit">Afficher</button>
</form>

<?php
if (isset($_GET['capteur'])) {
    $idc   = intval($_GET['capteur']);
    $debut = mysqli_real_escape_string($db, $_GET['debut']);
    $fin   = mysqli_real_escape_string($db, $_GET['fin']);

    // Security: make sure this sensor belongs to the manager's building
    $check = mysqli_query($db, "SELECT c.id_capteur FROM Capteur c
                                JOIN Salle s ON c.id_salle = s.id_salle
                                JOIN Batiment b ON s.id_batiment = b.id_batiment
                                WHERE c.id_capteur = $idc AND b.id_gestionnaire = $id_gest");
    if (mysqli_num_rows($check) > 0) {
        $q = "SELECT date_mesure, heure_mesure, valeur FROM Mesure
              WHERE id_capteur = $idc AND date_mesure BETWEEN '$debut' AND '$fin'
              ORDER BY date_mesure, heure_mesure";
        $r = mysqli_query($db, $q);
        echo "<h3>Mesures du capteur (".mysqli_num_rows($r)." résultat(s))</h3>";
        echo "<table><tr><th>Date</th><th>Heure</th><th>Valeur</th></tr>";
        while ($m = mysqli_fetch_assoc($r)) {
            echo "<tr><td>".htmlspecialchars($m['date_mesure'])."</td><td>".htmlspecialchars($m['heure_mesure'])."</td><td>".htmlspecialchars($m['valeur'])."</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>Ce capteur ne fait pas partie de votre bâtiment.</div>";
    }
}
include 'footer.php';
?>
