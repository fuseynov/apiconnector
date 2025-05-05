<?php

require_once 'api/ApiConnector.php';

// Paramètres de connexion
$apiBaseUrl = 'https://rest.unidata.msf.org/msf-mdm-unidata/rest/ud-api/v1/articles';
$login = 'DS_MSFSUP';
$password = 'LZcu8dtC';

try {
    $api = new ApiConnector($apiBaseUrl, $login, $password);

    $dateTimeSince = (new DateTime('-24 hours'))->format(DateTime::ATOM);

    $articles = $api->getArticlesUpdatedSince($dateTimeSince);

    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><articles></articles>');

    foreach ($articles as $article) {
        $articleNode = $xml->addChild('id', htmlspecialchars($article['id']));

        // Ajouter les balises qui m'intéressent
        if (isset($article['id'])) {
            $articleNode->addChild('id', htmlspecialchars($article['id']));
        }

        if (isset($article['title'])) {
            $articleNode->addChild('id', htmlspecialchars($article['title']));
        }

        if (isset($article['updated_at'])) {
            $articleNode->addChild('id', htmlspecialchars($article['updated_at']));
        }
    }

    // Sauvegarder le fichier XML
    $xmlFilePath = __DIR__ . 'export/articles_' . date('Ymd_His') . '.xml';
    if (!is_dir(dirname($xmlFilePath))) {
        mkdir(dirname($xmlFilePath), 0777, true);
    }
    $xml->asXML($xmlFilePath);

    echo "Fichir XML généré avec succés dans $xmlFilePath\n";

    // Dépôt du fichier distant 
    $remoteHost = 'your.sftp.server.com';
    $remoteUser = 'sftp_user';
    $remotePass = 'sftp_password';
    $remotePath = '/remote/path/' . basename($xmlFilePath);

    //$connection = ssh2_connect($remoteHost, 22);
    
    // if (!$connection) {
    //     throw new Exception('Impossible de se connecter au serveur distant.');
    // }

    // if (!ssh2_auth_password($connection, $remoteUser, $remotePass)) {
    //     throw new Exception('Échec de l\'authentification SSH.');
    // }

    // $sftp = ssh2_sftp($connection);

    // $stream = fopen("ssh2.sftp://$sftp$remotePath", 'w');
    // if (!$stream) {
    //     throw new Exception('Impossible d’ouvrir le fichier distant pour écriture.');
    // }

    // $localFile = fopen($xmlFilePath, 'r');
    // if (!$localFile) {
    //     throw new Exception('Impossible d’ouvrir le fichier local.');
    // }

    // while (!feof($localFile)) {
    //     fwrite($stream, fread($localFile, 8192));
    // }

    // fclose($localFile);
    // fclose($stream);

    // echo "Fichier transféré avec succès vers le serveur distant.\n";

} catch (\Throwable $th) {
    // echo 'Erreur : ' . $e->getMessage() . "\n";
    // @Todo : à logguer
}

?>