<?php
session_start();

$client_id = getenv('github_client_id');
$client_secret = getenv('github_client_secret');
$repo_database = getenv('github_repo_database');

$labels_on_repo_database = getenv('github_labels_on_repo_database');
//$labels_on_repo can also be obtained by hitting the below url:
//$get_labels_endpoint = "https://api.github.com/repos/".$repo_database."/labels";

$labels_on_repo_database = json_decode($labels_on_repo_database, true);

function request_get($url, $headers) {
    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);

    $formatted_headers = [
        'User-Agent: GithubPoweredForums',
        'Accept: application/vnd.github.squirrel-girl-preview'
    ];
    foreach($headers as $k => $v) {
        $formatted_headers[]= $k.': '.$v;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $formatted_headers);

    //So that curl_exec returns the contents of the cURL; rather than echoing it
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

    //execute post
    $result = curl_exec($ch);
    return $result;
}

function request_post($url, $post_data, $headers) {

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, true);

    //url-ify the data for the POST
    $fields_string = http_build_query($post_data);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

    $formatted_headers = [
        'User-Agent: GithubPoweredForums'
    ];
    foreach($headers as $k => $v) {
        $formatted_headers[]= $k.': '.$v;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $formatted_headers);

    //So that curl_exec returns the contents of the cURL; rather than echoing it
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

    //execute post

    /*
    $headers = [];
    curl_setopt($ch, CURLOPT_HEADERFUNCTION,
      function($curl, $header) use (&$headers)
      {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) // ignore invalid headers
          return $len;

        $headers[strtolower(trim($header[0]))][] = trim($header[1]);

        return $len;
      }
    );
    */
    $result = curl_exec($ch);
    //print_r($headers);die;
    return $result;
}


?>
