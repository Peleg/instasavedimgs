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

$username = 'brizoloves';
$password = getenv('BRIZO_INSTA_PASSWORD');
$ig = new \InstagramAPI\Instagram();
$ig->login($username, $password);

$uniqueUsernames = array_unique(getSavedUsernames($ig));

$fp = fopen('./out/users.csv', 'w');
fputcsv($fp, ['NAME', 'USERNAME', 'FOLLOWERS', 'URL', 'BIO']);

foreach($uniqueUsernames as $username) {
  $user = $ig->people->getInfoByName($username)->user;
  fputcsv($fp, [
    $user->full_name,
    $user->username,
    $user->follower_count,
    $user->external_url,
    $user->biography
  ]);
}

fclose($fp);

