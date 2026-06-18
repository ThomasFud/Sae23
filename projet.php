<?php
// ============================================================
//  Project management page (public)
// ============================================================
$title = "Gestion de projet";
include 'header.php';
?>
<h1>Gestion de projet</h1>

<h2>Organisation et planning</h2>
<div class="card">
  <p>Le projet a été découpé en plusieurs phases : analyse du cahier des charges et
  conception de la base de données, mise en place de la chaîne de traitement en conteneurs
  Docker, développement du flow Node-RED et du dashboard Grafana, puis réalisation du site
  web dynamique et de l'automatisation. Le diagramme de GANTT ci-dessous résume cette
  répartition dans le temps.</p>
  <img src="gantt.png" alt="Diagramme de GANTT du projet" style="max-width:100%; border-radius:10px; margin-top:10px;">
</div>

<h2>Outils collaboratifs</h2>
<div class="card">
  <p>Pour travailler ensemble, nous avons utilisé deux outils principaux :</p>
  <p><strong>GitHub</strong> pour la gestion de version du code : chaque évolution du site
  (connexion à la base, pages successives, authentification, administration) a fait l'objet
  d'un commit, ce qui permet de suivre l'avancement et de revenir en arrière en cas de problème.</p>
  <p><strong>Google Drive</strong> pour le partage des documents communs : cahier des charges,
  captures d'écran, comptes rendus, identifiants et brouillons des livrables, accessibles à
  tout le groupe en permanence.</p>
</div>

<h2>Synthèse par membre</h2>
<div class="card">
  <p><strong>Thomas :</strong> a travaillé principalement sur la partie technique : conception
  de la base de données, mise en place de la chaîne Docker (Mosquitto, Node-RED, InfluxDB,
  Grafana) et développement du site web en PHP/MySQL. Il a aussi assuré une partie de la
  coordination du groupe.</p>
  <p><strong>Célian :</strong> a participé au développement du flow Node-RED et à la création
  du dashboard Grafana, ainsi qu'aux tests de bout en bout de la chaîne de données.</p>
  <p><strong>Noé :</strong> a travaillé sur le script d'ingestion MQTT vers MySQL et son
  automatisation par crontab, et a contribué à la rédaction de la documentation.</p>
  <p><strong>Thibault :</strong> a réalisé le diagramme de GANTT, participé à la conception de
  la base de données et à la mise en forme des livrables et du site.</p>
</div>

<h2>Problèmes rencontrés et solutions</h2>
<div class="card">
  <p><strong>Connexion au broker MQTT :</strong> le broker de l'IUT n'était pas accessible en
  clair sur le port 1883 mais en TLS sur le port 8883 avec une authentification
  (student/student). Nous avons adapté la configuration de Node-RED et du script en conséquence.</p>
  <p><strong>Topic incorrect :</strong> au départ aucune donnée ne remontait. Le vrai topic
  commençait par <em>sensors/AM107/...</em> et non <em>AM107/...</em> comme prévu ; la correction
  de l'abonnement a débloqué la réception.</p>
  <p><strong>Droits Docker :</strong> des erreurs de permission empêchaient l'usage de Docker ;
  ajouter l'utilisateur au groupe docker a réglé le problème.</p>
  <p><strong>Écriture InfluxDB :</strong> Node-RED renvoyait une erreur de connexion. Il fallait
  utiliser le nom du conteneur (influxdb) et non localhost, car chaque conteneur a son propre
  réseau.</p>
</div>

<h2>Conclusion</h2>
<div class="card">
  <p>Le projet répond au cahier des charges : la chaîne complète fonctionne de bout en bout,
  des capteurs jusqu'à l'affichage. Les données sont collectées via MQTT, stockées dans
  InfluxDB et MySQL, visualisées dans Grafana et sur un site web dynamique sécurisé par
  sessions. Ce projet nous a permis de mettre en pratique le réseau, les conteneurs, les
  bases de données et le développement web sur une chaîne IoT réaliste.</p>
</div>

<?php include 'footer.php'; ?>


