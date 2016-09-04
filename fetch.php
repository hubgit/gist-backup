<?php

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

list($command, $username) = $argv;

if (!$username) {
    exit("Usage: $command username\n");
}

$client = new Client(['debug' => true]);

$dir = __DIR__ . '/gists/' . $username; // output directory

if (!file_exists($dir)) {
    mkdir($dir, 0700, true);
}

$url = sprintf('https://api.github.com/users/%s/gists', $username);

do {
    print $url . "\n";

    $response = $client->get($url, [
        'headers' => [
            'User-Agent' => 'hubgit/gist-backup',
            'Accept' => 'application/json',
        ]
    ]);

    $items = json_decode($response->getBody());

    foreach ($items as $item) {
        $id = $item->id;

        if (!preg_match('/^[0-9a-z]+$/', $id)) {
            exit($id . ' is not a valid Gist id');
        }

        $output = $dir . '/' . $id;

        // TODO: updates
        if (file_exists($output)) {
            print $output . " already exists\n";
            continue;
        }

        $command = sprintf('git clone %s %s', escapeshellarg($item->git_pull_url), escapeshellarg($output));
        print "> " . $command . "\n";
        exec($command);

        file_put_contents($output . '.json', json_encode($item));
    }


    $url = getNextLink($response);
} while ($url);

/**
 * @param Response $response
 *
 * @return string|null
 */
function getNextLink(Response $response) {
    foreach ($response->getHeader('Link') as $link) {
        if (preg_match('/<([^>]+?)>;\s*rel="next"/', $link, $matches)) {
            return $matches[1];
        }
    }

    return null;
}
