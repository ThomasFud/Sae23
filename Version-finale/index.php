<?php

//  Home page: site description, managed buildings & rooms

require 'db.php';          // open the database connection
$title = "Accueil";        // page title used by header.php
include 'header.php';       // print the page header and navigation
?>

<h1>Supervision des capteurs de l'IUT de Blagnac</h1>
<p>Ce site permet de visualiser les données relevées par les capteurs (température,
humidité) repartis dans les batiments de l'IUT. Les données sont collectees en temps
reel via un bus MQTT, stockées dans une base de données MySQL, puis pre
ésentées ici.</p>

<h2>Batiments gérés et salles equipées</h2>
<table>
  <tr><th>Batiment</th><th>Salle</th><th>Type</th><th>Capacité</th></tr>
<?php
// Fetch every room with its building (buildings without rooms still appear).
$sql = "SELECT b.nom AS batiment, s.nom AS salle, s.type, s.capacite
        FROM Batiment b
        LEFT JOIN Salle s ON s.id_batiment = b.id_batiment
        ORDER BY b.nom, s.nom";
$res = mysqli_query($db, $sql);

// One table row per room. htmlspecialchars() protects the output.
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
