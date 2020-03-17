<?php
include_once("forums_config.php");

if($_GET['act'] == 'get_threads') { //$_GET['forum']
    $url = 'https://api.github.com/search/issues?q=repo:'.$repo_database.'+is%3Aopen+label%3A"'.urlencode($_GET['forum']).'"&sort=updated&order=desc';
    $headers = [
        'Authorization' => 'token '.$_SESSION['github_access_token'],
    ];
    die(request_get($url, $headers));
}

if($_GET['act'] == 'get_thread') { //$_GET['thread_id']
    $url = "https://api.github.com/repos/".$repo_database."/issues/".urlencode($_GET['thread_id']);
    $headers = [
        'Authorization' => 'token '.$_SESSION['github_access_token'],
        'Accept' => "application/vnd.github.v3.html+json"
    ];
    die(request_get($url, $headers));
}

if($_GET['act'] == 'get_thread_comments') { //$_GET['thread_id']
    $url = "https://api.github.com/repos/".$repo_database."/issues/".urlencode($_GET['thread_id'])."/comments?per_page=25&page=".intval($_GET['page']);
    $headers = [
        'Authorization' => 'token '.$_SESSION['github_access_token'],
        'Accept' => "application/vnd.github.v3.html+json"
    ];
    die(request_get($url, $headers));
}

if($_GET['act'] == 'get_recent_active_threads') {
    $url = 'https://api.github.com/search/issues?q=repo:'.$repo_database.'+is%3Aopen&sort=updated&order=desc';
    $headers = [
        'Authorization' => 'token '.$_SESSION['github_access_token'],
    ];
    die(request_get($url, $headers));
}

if($_GET['act'] == 'search') { //$_GET['q']
    //To see all posts by a user, search 'involves:2min'
    $url = 'https://api.github.com/search/issues?q='.urlencode($_GET['q']).'+repo:'.$repo_database.'+is%3Aopen&sort=updated&order=desc';
    $headers = [
        'Authorization' => 'token '.$_SESSION['github_access_token'],
        'Accept' => "application/vnd.github.v3.text-match+json"
    ];
    die(request_get($url, $headers));
}

?>
