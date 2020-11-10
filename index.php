<?php
  $url = filter_input(INPUT_GET, 'url');
  $code = 200;
  $message = ' OK';

  if (!$url) {
    $test = array(
      'message' => '無効なリクエストです'
    );
    $code = 400;
    $message = ' Bad Request';

    $test = json_encode($test);
    header("HTTP/1.1 " . $code . $message);
    header('content-type: application/json; charset=utf-8');
    echo $test;
    exit;
  }

  $feed = file_get_contents($url);
  $feed = convert_namespace($feed);
  $obj = simplexml_load_string($feed);
  $json = json_encode($obj);

  function convert_namespace($xml) {
    $xml = preg_replace('/<((?!atom)[^>| ]+?):([^>]+?)>/', '<$1_$2>', $xml);
    return $xml;
  }

  header("HTTP/1.1 " . $code . $message);
  header('content-type: application/json; charset=utf-8');
  echo $json;
