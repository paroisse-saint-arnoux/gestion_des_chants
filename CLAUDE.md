# CLAUDE.md

Application PHP (sans framework) de préparation des feuilles de messe. Voir le README pour l'architecture.

## Environnement de dev (Docker)

- `docker compose up` : app sur http://localhost:8090, DBGate sur :3000, Mailpit, MySQL (base `gestion_des_chants`, root/root).
- Commandes PHP dans le conteneur : `docker compose exec -T app php bin/<script>.php`.
- Tests : `docker compose exec -T app vendor/bin/phpunit`.

## Tester les pages connectées (/app)

La connexion se fait normalement par lien magique email. Pour tester en ligne de commande :

```bash
docker compose exec -T app php bin/dev_login.php                 # premier admin
docker compose exec -T app php bin/dev_login.php --email=x@y.z   # utilisateur précis
```

Le script affiche un cookie `gdc_connexion=…` à passer à curl :

```bash
curl -s -b 'gdc_connexion=<valeur>' http://localhost:8090/app/sections/13346
```

Pour les formulaires POST, il faut aussi le jeton CSRF de la page (champ caché `_csrf`) et un cookie jar (`-c`/`-b jar.txt`) pour garder la session.
Révoquer les connexions ouvertes par le script : `docker compose exec -T app php bin/dev_login.php --revoke`.
Le script refuse de s'exécuter si `app.base_url` ne pointe pas sur localhost.
