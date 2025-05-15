<?php

require_once 'api/ApiConnector.php';
require_once 'config.php';

try {
    $api = new ApiConnector(API_BASE_URL, CLIENT_LOGIN, CLIENT_PWD);
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
                $articleNode->addChild('freeCode', htmlspecialchars($article['freeCode']) ? 'true' : 'false');
            }

            if (isset($article['group'])) {
                $groupNode = $articleNode->addChild('group');
                $groupNode->addChild('id', htmlspecialchars($article['group']['code']));
            }

            if (isset($article['family'])) {
                $familyNode = $articleNode->addChild('family');
                $familyNode->addChild('id', htmlspecialchars($article['family']['id']));
            }

            if (isset($article['root'])) {
                $rootNode = $articleNode->addChild('root');
                $rootNode->addChild('id', htmlspecialchars($article['root']['id']));
            }

            if (isset($article['labels'])) {
                $labelNode = $articleNode->addChild('labels');
                $labelNode->addChild('english', htmlspecialchars($article['labels']['english']));
                $labelNode->addChild('french', htmlspecialchars($article['labels']['french']));
                $labelNode->addChild('spanish', htmlspecialchars($article['labels']['spanish']));
            }

            if (isset($article['id'])) {
                $articleNode->addChild('msIdentifier', htmlspecialchars($article['id']));
            }

            if (isset($article['type'])) {
                $articleNode->addChild('type', htmlspecialchars($article['type']));
            }

            if (isset($article['standardizationLevel'])) {
                $articleNode->addChild('standardizationLevel', htmlspecialchars($article['standardizationLevel']));
            }

            if (isset($article['root']['status'])) {
                $rootNode = $articleNode->addChild('root');
                $statusNode = $rootNode->addChild('status');
                $statusNode->addChild('id', htmlspecialchars($article['root']['status']['id']));
            }

            if (isset($article['scSubscriptions'])) {
                $scSubNode = $articleNode->addChild('scSubscriptions');
                $scSubNode->addChild('msfSupply', $article['scSubscriptions']['msfSupply'] ? 'true' : 'false');
                $scSubNode->addChild('apu', $article['scSubscriptions']['apu'] ? 'true' : 'false');
                $scSubNode->addChild('msfLog', $article['scSubscriptions']['msfLog'] ? 'true' : 'false');
            }

            if (isset($article['ocSubscriptions'])) {
                $ocSubNode = $articleNode->addChild('ocSubscriptions');
                $ocSubNode->addChild('ocb', $article['ocSubscriptions']['ocb'] ? 'true' : 'false');
                $ocSubNode->addChild('oca', $article['ocSubscriptions']['oca'] ? 'true' : 'false');
                $ocSubNode->addChild('ocp', $article['ocSubscriptions']['ocp'] ? 'true' : 'false');
                $ocSubNode->addChild('ocg', $article['ocSubscriptions']['ocg'] ? 'true' : 'false');
                $ocSubNode->addChild('ocba', $article['ocSubscriptions']['ocba'] ? 'true' : 'false');
            }

            if (isset($article['medical'])) {
                $medicalNode   = $articleNode->addChild('medical');
                $medicalDGNode = $medicalNode->addChild('medicalDeviceGroup');
                $medicalDGNode->addChild('medicalDeviceClass', htmlspecialchars($article['medical']['medicalDeviceGroup']['medicalDevice']));
                $medicalNode->addChild('sterile', htmlspecialchars($article['medical']['sterile']));
            }

            // target euHsCode
            if (isset($article['supply']['euHsCode'])) {
                $supplyNode = $articleNode->addChild('supply');
                
                $supplyNode->addChild('hsCode', htmlspecialchars($article['supply']['euHsCode']));
            }

            if (isset($article['oldercode'])) {
                $articleNode->addChild('olderCode', htmlspecialchars($article['olderCode']));
            }
        }

        try {
            $xmlFilePath = __DIR__ . '/export/' . date('Ymd_His') . '.xml';
            
            if (!is_dir(dirname($xmlFilePath))) {
                mkdir(dirname($xmlFilePath), 0777, true);
            }

            $xml->asXML($xmlFilePath);
            echo "Fichier XML généré avec succés dans $xmlFilePath\n";
            
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