Parseur d'emploi du temps ADE52 campus vers ical.

Le projet est constitué de 2 scripts, le premier vise à être appelé périodiquement par cron. Ce script se connecte sur le serveur d'emploi du temps, s'authentifie, récupère l'intégralité de l'emploi du temps pour la "catégorie" donnée pour les 52 semaines de l'année.Un fichier est créé sur le serveur pour chaque catégorie (exemple : 1A, 2A promo...), ce fichier joue le rôle de cache et serra renvoyé au clients qui le demandent.

Un second script s'occupe de renvoyer un ical complet suivant les catégories demandées par le client, il se contente de concaténer des fichier du cache et de rajouter l'en tête ical.

Un troisième script est également disponible, il a pour but de permettre à l'utilisateur de simplement choisir les catégories qui l'intéresse; il renvoie une url qui peut être utilisée pour la demande.