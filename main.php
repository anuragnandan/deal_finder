<?php

require_once __DIR__."/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$sites = explode(",", getenv('SITES'));
$groceries = explode(",", getenv('GROCERIES'));

foreach($sites as $site)
{
  $ch = curl_init($site);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $text = curl_exec($ch);

  foreach($groceries as $groc)
  {
    $found = strpos($text, $groc);
    if ($found === false)
    {
      echo date("Y-m-d H:i:s"). ": $groc not found at $site \n";
    }
    else
    {
      echo date("Y-m-d H:i:s"). ": $groc found at $site \n";
      notify($site, $groc);
    }
  }
}

function notify($site, $groc)
{
  try {
    $client = new \GuzzleHttp\Client();
    $res = $client->request('POST', getenv('SMS_URL'), [
      'auth' => [getenv('SMS_TOKEN'), getenv('SMS_SECRET')],
      'json' => ["from" => getenv('PHONE_NUMBER'), "to" => "14045399428", "text" => "Found $groc -> $site"]
    ]);
  }
  catch(Exception $e)
  {
    echo date("Y-m-d H:i:s"). ": ". $e->getMessage();
  }
}
