<?php

class ApiConnector
{
    private string $baseUrl;
    private string $login;
    private string $password;
    private string $token;

    public function __construct(string $baseUrl, string $login, string $password, string $token = null)
    {
        $this->baseUrl  = $baseUrl;
        $this->login    = $login;
        $this->password = $password;
    }

    /**
     * Généreration du token pour l'authentification API
     * @throws Exception
     * @return string $token
     */
    public function getAccessToken(): ?string 
    {
        $urlToken = 'https://rest.unidata.msf.org/ebx-dataservices/rest/auth/v1/token:create';
        
        $data = [
            'login' => $this->login,
            'password' => $this->password
        ];

        $options = [
            CURLOPT_URL => $urlToken,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data)
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            throw new Exception("Erreur API lors de la génération du token. Code HTTP : " . $httpcode . ' - Réponse : ' . $response);
        } else {
            $responseData = json_decode($response, true);
            $accessToken  = $responseData['accessToken'] ?? null;
            if (isset($accessToken)) {
                return $accessToken;
            }
        }
        return null;
    }

    /**
     * Récupération des articles mis à jour depuis 24h
     * @param string $token
     * @throws Exception
     * @return array
     */
    public function getArticlesUpdatedSince24H(string $token): array 
    {
        $url = $this->baseUrl . '?login=' . $this->login . '&password=' . $this->password . '&filter=(date-greater-or-equal(./metaData/mostRecentUpdate,%27'. date('Y-m-d', strtotime('-1 day')) . 'T00:00:00.000%27))';

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Erreur CURL : ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Erreur API lors de la récupération des articles. Code HTTP : ' . $httpCode);
        }

        $articles = json_decode($response, true);

        if (!is_array($articles)) {
            throw new Exception('Format de réponse inattendu lors de la récupération des articles.');
        }

        return $articles;
    }
}
