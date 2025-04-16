<?php
// Version simple
phpinfo();

// Ou version plus complète
echo '<h2>Vérification des extensions</h2>';
echo 'OpenSSL: ', extension_loaded('openssl') ? '✅ Activé' : '❌ Désactivé', '<br>';
echo 'cURL: ', extension_loaded('curl') ? '✅ Activé' : '❌ Désactivé', '<br>';
echo 'PDO MySQL: ', extension_loaded('pdo_mysql') ? '✅ Activé' : '❌ Désactivé', '<br>';
echo 'MySQLi: ', extension_loaded('mysqli') ? '✅ Activé' : '❌ Désactivé';