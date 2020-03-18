<?php
if (!isset($_GET['user']) or !isset($_GET['liveChatId'])) {echo 'Доступ закрыт'; exit();}
$liveChatId = $_GET['liveChatId'];
$user = $_GET['user'];

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/refreshToken.php';
require_once __DIR__ . '/mysqlConnect.php';

$client = new Google_Client();
$client->setAuthConfig('client_secret.json');
$client->setScopes([
    'https://www.googleapis.com/auth/youtube.readonly',
]);
$client->setAccessType("offline");

$sql = "select google_json from users where user = '$user'";
$result = mysqli_query($db, $sql);
$data = json_decode(mysqli_fetch_array($result)[0], true);
if (($data['created'] - time()) <= -3600) {
    $data = refreshToken($data['refreshToken'], $user, $db);
}
$client->setAccessToken($data['accessToken']);

$service = new Google_Service_YouTube($client);

$queryParams = [
    'maxResults' => 2000,
    'profileImageSize' => 120
];


$response = $service->liveChatMessages->listLiveChatMessages($liveChatId, 'id,snippet,authorDetails', $queryParams);
// $response['items'] - messages list
echo json_encode($response);