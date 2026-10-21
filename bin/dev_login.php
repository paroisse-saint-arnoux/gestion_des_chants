<?php

declare(strict_types=1);

/**
 * Script de développement : ouvre une connexion pour un utilisateur sans passer
 * par le lien magique envoyé par email, afin de tester les pages de /app en
 * ligne de commande (curl).
 *
 * Crée une connexion persistante (table « connexions », cf. App\Auth) et
 * affiche la valeur du cookie gdc_connexion à passer à curl. Aucun lien
 * cliquable n'est produit : le cookie est httponly et ne s'utilise qu'en
 * l'envoyant explicitement (curl -b).
 *
 * Réservé au développement : refuse de s'exécuter si app.base_url ne pointe
 * pas sur localhost.
 *
 * Usage :
 *   docker compose exec app php bin/dev_login.php
 *   docker compose exec app php bin/dev_login.php --email=chantre@example.org
 *   docker compose exec app php bin/dev_login.php --revoke
 *
 * Options :
 *   --email=<email>   Utilisateur à connecter (défaut : premier administrateur).
 *   --revoke          Supprime toutes les connexions ouvertes par ce script.
 *   -h, --help        Cette aide.
 */

use App\Database;
use App\Models\Connexion;
use App\Models\Utilisateur;

if (PHP_SAPI !== 'cli') {
    exit("Ce script s'exécute en ligne de commande uniquement.\n");
}

require dirname(__DIR__) . '/src/bootstrap.php';

/** Marque les connexions ouvertes par ce script (colonne user_agent). */
const DEV_LOGIN_UA = 'bin/dev_login.php';

$opts = getopt('h', ['email:', 'revoke', 'help']);

if (isset($opts['h']) || isset($opts['help'])) {
    $doc = (string) file_get_contents(__FILE__);
    echo substr($doc, (int) strpos($doc, '/**'), (int) strpos($doc, '*/') + 2 - (int) strpos($doc, '/**')), "\n";
    exit(0);
}

$baseUrl = rtrim((string) config('app.base_url', ''), '/');
$hote = (string) parse_url($baseUrl, PHP_URL_HOST);
if (!in_array($hote, ['localhost', '127.0.0.1'], true)) {
    fwrite(STDERR, "Refusé : app.base_url ({$baseUrl}) n'est pas une adresse locale. Script réservé au développement.\n");
    exit(1);
}

if (isset($opts['revoke'])) {
    $n = Database::all('SELECT id FROM connexions WHERE user_agent = ?', [DEV_LOGIN_UA]);
    Database::delete('connexions', ['user_agent' => DEV_LOGIN_UA]);
    echo count($n), " connexion(s) révoquée(s).\n";
    exit(0);
}

if (isset($opts['email'])) {
    $user = Utilisateur::findByEmail((string) $opts['email']);
} else {
    $user = Database::one("SELECT * FROM utilisateurs WHERE type = 'admin' ORDER BY id LIMIT 1");
}
if ($user === null) {
    fwrite(STDERR, "Utilisateur introuvable.\n");
    exit(1);
}

$_SERVER['HTTP_USER_AGENT'] = DEV_LOGIN_UA;
$cookie = Connexion::create((int) $user['id']);

echo "Connecté : {$user['email']} ({$user['type']}, paroisse #{$user['paroisse_id']})\n";
echo "Cookie   : gdc_connexion={$cookie}\n\n";
echo "Exemple  : curl -s -b 'gdc_connexion={$cookie}' {$baseUrl}/app\n";
echo "Révoquer : php bin/dev_login.php --revoke\n";
