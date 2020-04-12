<?php
if (!isset($_GET['user'])) {
    echo 'Доступ закрыт'; exit();
}
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

$sql = "select google_json from users where user = ':user'";
$result = query($db, $sql, ['user' => $user]);
$data = json_decode(mysqli_fetch_array($result)[0], true);
if (($data['created'] - time()) <= -3600) {
    $data = refreshToken($data['refreshToken'], $user, $db);
}

$client->setAccessToken($data['accessToken']);
$service = new Google_Service_YouTube($client);

$queryParams = [
    'broadcastStatus' => 'upcoming',
    'maxResults' => 50
];
$response = $service->liveBroadcasts->listLiveBroadcasts('id,snippet,status', $queryParams);
$items = $response['items'];
$streamsUpcoming = itemsGenerator($items);

$queryParams = [
    'broadcastStatus' => 'active',
    'maxResults' => 50
];
$response = $service->liveBroadcasts->listLiveBroadcasts('id,snippet,status', $queryParams);
$items = $response['items'];
$streamsActive = itemsGenerator($items);
$streams = array_merge($streamsUpcoming, $streamsActive);

echo json_encode($streams);

function itemsGenerator($items) {
    $streams = [];
    foreach ($items as $item) {
        // значит трансляция в эфире или заплпнированна (законченные не выводим)
        if (!isset($item['snippet']['actualEndTime'])) {
            $streams[] = [
                'id' => $item['id'],
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'image' => $item['snippet']['thumbnails']['medium']['url'],
                'liveChatId' => $item['snippet']['liveChatId'],
                'channelId' => $item['snippet']['channelId']
            ];
        }
    }
    return $streams;
}


















exit();
$user = '+7999999999';

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
}
require_once __DIR__ . '/vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('client_secret.json');
$client->setScopes([
    'https://www.googleapis.com/auth/youtube.readonly',
]);
$client->setAccessType("offline");

$sql = "select google_json from users where recepient = '$user'";
$result = mysqli_query($db, $sql);
$result = json_decode(mysqli_fetch_array($result)[0], true);
$accessToken = $result['accessToken'];
echo $accessToken;
$client->setAccessToken($accessToken);

// Define service object for making API requests.
$service = new Google_Service_YouTube($client);

$queryParams = [
    'broadcastStatus' => 'all'
];

$response = $service->liveBroadcasts->listLiveBroadcasts('id,snippet,contentDetails,status', $queryParams);
print_r($response);
