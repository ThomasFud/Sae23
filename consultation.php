<?php
// ============================================================
//  Public page: latest measurement of every sensor
// ============================================================
require 'db.php';
$title = "Consultation";
include 'header.php';
?>

<h1>Consultation des dernieres mesures</h1>
<p>Derniere valeur relevee pour chaque capteur, accessible a tous.</p>

<table>
  <tr><th>Batiment</th><th>Salle</th><th>Capteur</th><th>Valeur</th><th>Date</th><th>Heure</th></tr>
<?php
// Highest id_mesure per sensor = most recent measurement
$sql = "SELECT b.nom AS batiment, s.nom AS salle, c.type AS type, c.unite AS unite,
               m.valeur, m.date_mesure, m.heure_mesure
        FROM Mesure m
        JOIN Capteur c ON m.id_capteur = c.id_capteur
        JOIN Salle s   ON c.id_salle   = s.id_salle
        JOIN Batiment b ON s.id_batiment = b.id_batiment
        WHERE m.id_mesure IN (SELECT MAX(id_mesure) FROM Mesure GROUP BY id_capteur)
        ORDER BY b.nom, s.nom, c.type";
$res = mysqli_query($db, $sql);
while ($row = mysqli_fetch_assoc($res)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['batiment']) . "</td>";
    echo "<td>" . htmlspecialchars($row['salle']) . "</td>";
    echo "<td>" . htmlspecialchars($row['type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['valeur']) . " " . htmlspecialchars($row['unite']) . "</td>";
    echo "<td>" . htmlspecialchars($row['date_mesure']) . "</td>";
    echo "<td>" . htmlspecialchars($row['heure_mesure']) . "</td>";
    echo "</tr>";
}
?>
</table>

<?php include 'footer.php'; ?>
