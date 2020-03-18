<?php
$dbhost='localhost';
$dbuser='root';
$dbpassword='';
$database='youtube';
$db = mysqli_connect($dbhost, $dbuser, $dbpassword, $database)
or die("Connection Error: " . mysqli_connect_error());

function query($db, $sqlQuery, $params = [], $getQuery = false) {
    if (count($params) > 0) {
        foreach ($params as $key => $param) {
            $sqlQuery = preg_replace('/(:' . $key . '\b)/', mysqli_real_escape_string($db, $param),
                $sqlQuery);
        }
    }
    if ($getQuery) return $sqlQuery;
    $res = mysqli_query( $db, $sqlQuery );
    return $res;
}


