<?php

require __DIR__ . '/vendor/autoload.php';

if (count($argv) !== 2) {
    exit("Usage: {$argv[0]} username\n");
}

list($command, $username) = $argv;

$dir = __DIR__ . '/gists/' . $username; // output directory

if (!file_exists($dir)) {
    mkdir($dir, 0700, true);
}

$collection = new \WebResource\Collection(sprintf(
    'https://api.github.com/users/%s/gists',
    $username
));

foreach ($collection as $item) {
    $id = $item['id'];

    if (!preg_match('/^[0-9a-z]+$/', $id)) {
        exit($id . ' is not a valid Gist id');
    }

    $output = $dir . '/' . $id;

    // TODO: updates
    if (file_exists($output)) {
        print $output . " already exists\n";
        continue;
    }

    $command = sprintf(
        'git clone %s %s',
        escapeshellarg($item['git_pull_url']),
        escapeshellarg($output)
    );
    print "> " . $command . "\n";
    exec($command);

    file_put_contents($output . '.json', json_encode($item));
}
