<?php

require __DIR__.'/vendor/autoload.php';

//
// debugger: eval(\Psy\sh());
//

function getSavedUsernames($ig, $maxId = false) {
  $response = $ig
    ->request('feed/saved' . ($maxId ? "?max_id=$maxId" : ''))
    ->getResponse(new \InstagramAPI\Response\SavedFeedResponse);

  $usernames = array_map(function ($item) {
    return $item->media->user->username;
  }, $response->items);

  if ($response->isMoreAvailable()) {
    return array_merge(
      $usernames,
      getSavedUsernames($ig, $response->getNextMaxId())
    );
  }

  return $usernames;
}

function tryGetEmail($webUrl, $bio) {
  $emailRegex = '/([0-9a-z_\.-]+@[0-9a-z_-]+[\.a-z]+)/i';

  if (preg_match($emailRegex, $webUrl, $matches)) { // url IS email
    return $matches[0];
  }

  if (preg_match($emailRegex, $bio, $matches)) { // bio HAS email
    return $matches[0];
  }

  if ($webUrl) {
    // crawl site. hope for the best
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
    try {
      $html = curl_exec($ch);
      if (preg_match($emailRegex, $html, $matches)) { // site has email
        return $matches[0];
      }
    } catch(Exception $e) { /* dont care */ }
  }

  return '';
}

function getCleanName($name) {
  $nameRegex = "~([a-zA-Z0-9_ !@#$%^&*();\\\/|<>\"'+.,:?=-]+)~";
  if (preg_match($nameRegex, $name, $matches)) { // bio HAS email
    return $matches[0];
  }
  return $name ?: 'there';
}

$username = 'brizoloves';
$password = getenv('BRIZO_INSTA_PASSWORD');
$ig = new \InstagramAPI\Instagram();
$ig->login($username, $password);

$uniqueUsernames = array_unique(getSavedUsernames($ig));

$fp = fopen('./out/users.csv', 'w');
fputcsv($fp, ['NAME', 'USERNAME', 'FOLLOWERS', '', 'EMAIL', 'URL', 'BIO']);

foreach($uniqueUsernames as $username) {
  $user = $ig->people->getInfoByName($username)->user;
  fputcsv($fp, [
    getCleanName($user->full_name),
    $user->username,
    $user->follower_count,
    tryGetEmail($user->external_url, $user->biography),
    '', // email link column
    $user->external_url,
    $user->biography
  ]);
}

fclose($fp);

