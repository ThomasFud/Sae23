<?php
// ============================================================
//  Home page: site description, managed buildings & rooms
// ============================================================
require 'db.php';
$title = "Accueil";
include 'header.php';
?>

<h1>Supervision des capteurs de l'IUT de Blagnac</h1>
<p>Ce site permet de visualiser les donnees relevees par les capteurs (temperature,
humidite) repartis dans les batiments de l'IUT. Les donnees sont collectees en temps
reel via un bus MQTT, stockees dans une base de donnees MySQL, puis presentees ici.</p>

<h2>Batiments geres et salles equipees</h2>
<table>
  <tr><th>Batiment</th><th>Salle</th><th>Type</th><th>Capacite</th></tr>
<?php
$sql = "SELECT b.nom AS batiment, s.nom AS salle, s.type, s.capacite
        FROM Batiment b
        LEFT JOIN Salle s ON s.id_batiment = b.id_batiment
        ORDER BY b.nom, s.nom";
$res = mysqli_query($db, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['batiment']) . "</td>";
    echo "<td>" . htmlspecialchars($row['salle']) . "</td>";
    echo "<td>" . htmlspecialchars($row['type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['capacite']) . "</td>";
    echo "</tr>";
}
?>
</table>

<h2>Mentions legales</h2>
<div class="legal">
  <p>Site realise dans le cadre de la SAE23 du BUT Reseaux &amp; Telecommunications,
  IUT de Blagnac. Donnees issues des capteurs AM107.</p>
</div>

<?php include 'footer.php'; ?>
