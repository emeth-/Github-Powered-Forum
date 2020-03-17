<?php

include_once('forums_config.php');

/*
https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/#web-application-flow
*/

$url = "https://github.com/login/oauth/access_token";
$post_data = [
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'code' => $_GET['code']
];
$headers = [
    'Accept' => 'application/json',
];
$results = request_post($url, $post_data, $headers);
$auth_results = json_decode($results, true);

$_SESSION['github_access_token'] = $auth_results['access_token'];

$url = "https://api.github.com/user";
$headers = [
    'Authorization' => 'token '.$_SESSION['github_access_token']
];
$github_data_str = request_get($url, $headers);
$github_data = json_decode($github_data_str, true);

if(!$github_data['login']) {
    $github_data['login'] = '';
}

$_SESSION['github_username'] = $github_data['login'];

header("Location: index.php");
die();

?>
