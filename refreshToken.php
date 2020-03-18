<?php
require_once __DIR__ . '/vendor/autoload.php';

/**
 * @param string $refreshToken
 * @param string $userForUpdate
 * @param $db
 * @return array
 * @throws Google_Exception
 */
function refreshToken(string $refreshToken, string $userForUpdate, $db):array
{
    $client = new Google_Client();
    $client->setAuthConfig('client_secret.json');
    $client->setScopes([
        'https://www.googleapis.com/auth/youtube.readonly',
    ]);
    $client->setAccessType("offline");

    $client->refreshToken($refreshToken);
    $accessToken = $client->getAccessToken();

    $refreshToken = $accessToken['refresh_token'];
    $accessToken = $accessToken['access_token'];
    $clientId = $client->getClientId();
    $data = [
        'refreshToken' => $refreshToken,
        'accessToken' => $accessToken,
        'tknType' => 'Bearer',
        'clientId' => $clientId,
        'created' => time()
    ];

    $json = json_encode($data);
    $sql = "update users set google_json = ':google_json' where user = '$userForUpdate'";
    query($db, $sql, ['google_json' => $json]);

    return $data;
}

