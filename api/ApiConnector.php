<?php

class ApiConnector
{
    private string $baseUrl;
    private string $login;
    private string $password;

    public function __construct(string $baseUrl, string $login, string $password)
    {
        $this->baseUrl  = $baseUrl;
        $this->login    = $login;
        $this->password = $password;
        $this->authenticate();
    }

    private function authenticate(): void
    {   
        $data = [
            'login' => $this->login,
            'password' => $this->password
        ];

        $url = $this->baseUrl . '?login=' . $this->login . '&password=' . $this->password;

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true, 
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
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
            throw new Exception("Erreur API lors de l'authentification. Code HTTP : " . $httpCode);
        }

        $responseData = json_decode($response, true);
    }

    public function getArticlesUpdatedSince(string $dateTime): array 
    {
        $url = $this->baseUrl . '/articles?updated_since=' . urlencode($dateTime);

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
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
