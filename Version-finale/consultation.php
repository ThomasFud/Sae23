<?php
// ============================================================
//  Public page: latest measurement of every sensor
//  + trend chart (last 3 measures per sensor)
// ============================================================
require 'db.php';
$title = "Consultation";
include 'header.php';
?>
<!-- Load Chart.js from a CDN to draw the trend chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<h1>Consultation des dernières mesures</h1>
<p>Dernière valeur relevée pour chaque capteur, accessible à tous.</p>

<table>
  <tr><th>Bâtiment</th><th>Salle</th><th>Capteur</th><th>Valeur</th><th>Date</th><th>Heure</th></tr>
<?php
// Get the latest measurement of each sensor.
// The subquery keeps only the highest id_mesure per sensor (the most recent).
$sql = "SELECT b.nom AS batiment, s.nom AS salle, c.type AS type, c.unite AS unite,
               m.valeur, m.date_mesure, m.heure_mesure
        FROM Mesure m
        JOIN Capteur c ON m.id_capteur = c.id_capteur
        JOIN Salle s   ON c.id_salle   = s.id_salle
        JOIN Batiment b ON s.id_batiment = b.id_batiment
        WHERE m.id_mesure IN (SELECT MAX(id_mesure) FROM Mesure GROUP BY id_capteur)
        ORDER BY b.nom, s.nom, c.type";
$res = mysqli_query($db, $sql);

// Print one row per sensor with its latest value.
while ($row = mysqli_fetch_assoc($res)) {
    echo "<tr>";
    echo "<td>".htmlspecialchars($row['batiment'])."</td>";
    echo "<td>".htmlspecialchars($row['salle'])."</td>";
    echo "<td>".htmlspecialchars($row['type'])."</td>";
    echo "<td>".htmlspecialchars($row['valeur'])." ".htmlspecialchars($row['unite'])."</td>";
    echo "<td>".htmlspecialchars($row['date_mesure'])."</td>";
    echo "<td>".htmlspecialchars($row['heure_mesure'])."</td>";
    echo "</tr>";
}
?>
</table>

<?php
// Build one chart line (dataset) per sensor, using its last 3 measures.
$caps = mysqli_query($db, "SELECT c.id_capteur, s.nom AS salle, c.type
                           FROM Capteur c
                           JOIN Salle s ON c.id_salle = s.id_salle
                           ORDER BY s.nom, c.type");
// Color palette reused in turn for each sensor line.
$colors = array("#2563eb","#06b6d4","#f59e0b","#ef4444","#10b981","#8b5cf6","#ec4899","#14b8a6");
$datasets = array();
$i = 0;
while ($cap = mysqli_fetch_assoc($caps)) {
    $idc = intval($cap['id_capteur']);
    // Last 3 measures of this sensor, newest first.
    $r = mysqli_query($db, "SELECT valeur FROM Mesure WHERE id_capteur = $idc
                            ORDER BY id_mesure DESC LIMIT 3");
    $vals = array();
    while ($m = mysqli_fetch_assoc($r)) { $vals[] = floatval($m['valeur']); }
    $vals = array_reverse($vals);   // reorder oldest -> newest for the chart
    if (count($vals) === 0) continue;  // skip sensors with no measure yet
    // One dataset = one sensor line on the chart.
    $datasets[] = array(
        "label" => $cap['salle']." - ".$cap['type'],
        "data" => $vals,
        "borderColor" => $colors[$i % count($colors)],
        "backgroundColor" => "transparent",
        "tension" => 0.3,
        "pointRadius" => 3
    );
    $i++;
}
?>

<?php if (count($datasets) > 0): ?>
<h2>Tendance (3 dernières mesures par capteur)</h2>
<div style="background:#fff;border-radius:14px;padding:16px;box-shadow:0 6px 24px rgba(15,23,42,.07);margin-top:12px;">
  <canvas id="chartTrend" height="120"></canvas>
</div>
<script>
// Create the line chart. The PHP datasets array is turned into JSON
// so Chart.js can read it directly.
new Chart(document.getElementById("chartTrend"), {
  type: "line",
  data: {
    labels: ["M-2", "M-1", "Actuelle"],   // the 3 positions on the X axis
    datasets: <?php echo json_encode($datasets); ?>
  },
  options: {
    responsive: true,
    plugins: { legend: { position: "bottom" } }
  }
});
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>
