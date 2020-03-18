<?php
session_start();

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
} else {
    if (!isset($_GET['user'])) {
        echo 'Доступ закрыт';
        exit();
    } else {
        $_SESSION['user'] = $_GET['user'];
        $user = $_GET['user'];
    }
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/mysqlConnect.php';

$client = new Google_Client();
// это путь до нашего файла
$client->setAuthConfig('client_secret.json');
// чуть позже расскажу
$client->setScopes([
    'https://www.googleapis.com/auth/youtube.readonly',
]);
// Требуется для получения refresh_token
$client->setAccessType("offline");

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
} else {
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    exit();
}

$refreshToken = $_SESSION['access_token']['refresh_token'];
$accessToken = $_SESSION['access_token']['access_token'];
$tknType = $_SESSION['access_token']['token_type'];
$clientId = $client->getClientId();
$created = $_SESSION['access_token']['created'];
unset($_SESSION['access_token']);
unset($_SESSION['user']);
session_destroy();

$data = json_encode([
  'refreshToken' => $refreshToken,
    'accessToken' => $accessToken,
    'tknType' => $tknType,
    'clientId' => $clientId,
    'created' => $created
]);
echo $data;


$sql = "update users set google_json = ':google_json' where user = ':user'";
$result = query($db, $sql, ['google_json' => $data, 'user' => $user]);
if (!$result) echo 'mysql error';
//else echo 'Данные успешно обновленны';