<?php

require_once 'api/ApiConnector.php';

// Paramètres de connexion
$apiBaseUrl = 'https://rest.unidata.msf.org/msf-mdm-unidata/rest/ud-api/v1/articles';
$login = 'DS_MSFSUP';
$password = 'LZcu8dtC';

try {
    $api = new ApiConnector($apiBaseUrl, $login, $password);
    $token = $api->getAccessToken();
    if (isset($api)) {
        echo "✅ Connexion réussie !\n";
        $responseData = $api->getArticlesUpdatedSince24H($token);
        
        $articles = $responseData['rows'];
        
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><articles></articles>');

        foreach ($articles as $article) {
            $articleNode = $xml->addChild('article');

            if (isset($article['code'])) {
                $articleNode->addChild('code', htmlspecialchars($article['code']));
            }

            if (isset($article['freeCode'])) {
                $articleNode->addChild('freeCode', htmlspecialchars($article['freeCode']));
            }

            if (isset($article['group']['id'])) {
                $articleNode->addChild('groupId', htmlspecialchars($article['group']['id']));
            }

            if (isset($article['family']['id'])) {
                $articleNode->addChild('familyId', htmlspecialchars($article['family']['id']));
            }

            if (isset($article['root']['id'])) {
                $articleNode->addChild('rootId', htmlspecialchars($article['root']['id']));
            }

            if (isset($article['labels']['french'])) {
                $articleNode->addChild('labelFr', htmlspecialchars($article['labels']['french']));
            }

            if (isset($article['labels']['english'])) {
                $articleNode->addChild('labelEn', htmlspecialchars($article['labels']['english']));
            }

            if (isset($article['labels']['spanish'])) {
                $articleNode->addChild('labelSp', htmlspecialchars($article['labels']['spanish']));
            }

            // MSFIDENTIFIER A RAJOUTER

            if (isset($article['type'])) {
                $articleNode->addChild('type', htmlspecialchars($article['type']));
            }

            if (isset($article['standardizationLevel'])) {
                $articleNode->addChild('standardizationLevel', htmlspecialchars($article['standardizationLevel']));
            }

            // ID OU LABEL ?
            if (isset($article['root']['status']['id'])) {
                $articleNode->addChild('status', htmlspecialchars($article['root']['status']['id']));
            }

            if (isset($article['scSubscriptions']['msfSupply'])) {
                $articleNode->addChild('msfSupply', $article['scSubscriptions']['msfSupply'] ? 'true' : 'false');
            }

            if (isset($article['scSubscriptions']['apu'])) {
                $articleNode->addChild('apu', $article['scSubscriptions']['apu'] ? 'true' : 'false');
            }

            if (isset($article['scSubscriptions']['msfLog'])) {
                $articleNode->addChild('msfLog', $article['scSubscriptions']['msfLog'] ? 'true' : 'false');
            }

            if (isset($article['ocSubscriptions']['ocb'])) {
                $articleNode->addChild('ocb', $article['ocSubscriptions']['ocb'] ? 'true' : 'false');
            }

            if (isset($article['ocSubscriptions']['oca'])) {
                $articleNode->addChild('oca', $article['ocSubscriptions']['oca'] ? 'true' : 'false');
            }

            if (isset($article['ocSubscriptions']['ocp'])) {
                $articleNode->addChild('ocp', $article['ocSubscriptions']['ocp'] ? 'true' : 'false');
            }

            if (isset($article['ocSubscriptions']['ocg'])) {
                $articleNode->addChild('ocg', $article['ocSubscriptions']['ocg'] ? 'true' : 'false');
            }

            if (isset($article['ocSubscriptions']['ocba'])) {
                $articleNode->addChild('ocba', $article['ocSubscriptions']['ocba'] ? 'true' : 'false');
            }

            if (isset($article['medical']['medicalDeviceGroup']['medicalDevice'])) {
                $articleNode->addChild('medicalDeviceClass', htmlspecialchars($article['medical']['medicalDeviceGroup']['medicalDevice']));
            }

            if (isset($article['medical']['sterile'])) {
                $articleNode->addChild('sterile', htmlspecialchars($article['medical']['sterile']));
            }

            // target euHsCode
            if (isset($article['supply']['euHsCode'])) {
                $articleNode->addChild('hsCode', htmlspecialchars($article['supply']['euHsCode']));
            }

            if (isset($article['oldercode'])) {
                $articleNode->addChild('oldercode', htmlspecialchars($article['oldercode']));
            }

        }

        try {
            $xmlFilePath = __DIR__ . '/export/articles_' . date('Ymd_His') . '.xml';
            
            if (!is_dir(dirname($xmlFilePath))) {
                mkdir(dirname($xmlFilePath), 0777, true);
            }

            $xml->asXML($xmlFilePath);
            echo "Fichir XML généré avec succés dans $xmlFilePath\n";
            
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;

            $dom->loadXML($xml->asXML());
            $domPath = __DIR__ . '/export/articles_' . date('Ymd_His') . '.xml';
            $dom->save($domPath);

            echo "✅ XML formaté sauvegardé dans : $domPath\n";

        } catch (Exception $e) {
            echo "Erreur lors de la génération du fichier XML ". $e->getMessage() ."";
        }
    }


    // Dépôt du fichier distant 
    // $remoteHost = 'your.sftp.server.com';
    // $remoteUser = 'sftp_user';
    // $remotePass = 'sftp_password';
    // $remotePath = '/remote/path/' . basename($xmlFilePath);

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

} catch (Exception $e) {
    echo "❌ Erreur lors de la connexion : " . $e->getMessage() . "\n";
    // @Todo : à logguer
}

?>