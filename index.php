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

  $parsed = parse_url($url);
  $allowed_schemes = ['http', 'https'];
  if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], $allowed_schemes, true)) {
    $test = array(
      'message' => '許可されていないURLスキームです'
    );
    $code = 400;
    $message = ' Bad Request';

    $test = json_encode($test);
    header("HTTP/1.1 " . $code . $message);
    header('content-type: application/json; charset=utf-8');
    echo $test;
    exit;
  }

  $host = $parsed['host'] ?? '';
  if (empty($host)) {
    $test = array(
      'message' => '無効なURLです'
    );
    $code = 400;
    $message = ' Bad Request';

    $test = json_encode($test);
    header("HTTP/1.1 " . $code . $message);
    header('content-type: application/json; charset=utf-8');
    echo $test;
    exit;
  }

  $ip = gethostbyname($host);
  if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
    $test = array(
      'message' => 'ホスト名を解決できません'
    );
    $code = 400;
    $message = ' Bad Request';

    $test = json_encode($test);
    header("HTTP/1.1 " . $code . $message);
    header('content-type: application/json; charset=utf-8');
    echo $test;
    exit;
  }

  if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    $test = array(
      'message' => '許可されていないIPアドレスです'
    );
    $code = 403;
    $message = ' Forbidden';

    $test = json_encode($test);
    header("HTTP/1.1 " . $code . $message);
    header('content-type: application/json; charset=utf-8');
    echo $test;
    exit;
  }

  $referer = $_SERVER['HTTP_REFERER'] ?? '';
  $allowed_domain = $_SERVER['HTTP_HOST'] ?? '';

  if (empty($referer) || strpos($referer, $allowed_domain) === false) {
    $test = array(
      'message' => 'アクセスが許可されていません'
    );
    $code = 403;
    $message = ' Forbidden';

    $test = json_encode($test);
    header("HTTP/1.1 " . $code . $message);
    header('content-type: application/json; charset=utf-8');
    echo $test;
    exit;
  }

  $context = stream_context_create([
    'http' => [
      'follow_location' => 0,
      'max_redirects' => 0
    ]
  ]);
  $feed = file_get_contents($url, false, $context);
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
