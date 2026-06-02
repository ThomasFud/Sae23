SAE23 - Site web de supervision
===============================

Installation
------------
1. Copier tous ces fichiers dans : /opt/lampp/htdocs/sae23/
2. Acceder au site : http://localhost/sae23/   (index.php est servi automatiquement)

Comptes (mots de passe stockes en MD5 dans la base)
---------------------------------------------------
- Administrateur :  login = admin    / mot de passe = admin
- Gestionnaire E :  login = gest_e   / mot de passe = student
- Gestionnaire C :  login = gest_c   / mot de passe = student

Pages
-----
- index.php         Accueil (public) : description, batiments, salles
- consultation.php  Consultation (public) : derniere mesure de chaque capteur
- gestion.php       Gestion (gestionnaire) : min/max/moyenne + filtre par capteur/periode
- admin.php         Administration (admin) : ajout/suppression batiments, salles, capteurs
- projet.php        Gestion de projet : GANTT, outils, synthese (A COMPLETER)
- login.php         Connexion ; logout.php Deconnexion
- db.php            Connexion mysqli (incluse partout)
- auth.php          Gestion des sessions / roles
- header.php / footer.php / style.css   Mise en page commune

Securite
--------
Les pages gestion.php et admin.php sont protegees par session (fonction exiger_role).
Un gestionnaire ne voit que les capteurs de SON batiment.
