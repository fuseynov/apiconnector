<?php 

require_once 'api/ApiConnector.php';

// Paramètres de connexion
$apiBaseUrl = 'https://rest.unidata.msf.org/msf-mdm-unidata/rest/ud-api/v1/articles';
$login = 'DS_MSFSUP';
$password = 'LZcu8dtC';

try {
    $api = new ApiConnector($apiBaseUrl, $login, $password);
    echo "✅ Connexion réussie !\n";
} catch (\Exception $e) {
    echo "❌ Erreur lors de la connexion : " . $e->getMessage() . "\n";
}

?>