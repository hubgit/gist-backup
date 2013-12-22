<?php

require __DIR__ . '/headers.php';

list($command, $username) = $argv;

if (!$username) {
	exit("Usage: $command username\n");
}

$context = stream_context_create(array('http' => array('user_agent' => 'hubgit/gist-backup')));

$url = sprintf('https://api.github.com/users/%s/gists', $username);
$dir = __DIR__ . '/gists-' . $username; // output directory

do {
	print $url . "\n";

	$json = file_get_contents($url, false, $context);
	$items = json_decode($json);

	foreach ($items as $item) {
		$id = $item->id;

		if (!preg_match('/^\d+$/', $id)) {
			exit($id . ' is not an integer Gist id');
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

	$links = parseLinkResponseHeaders($http_response_header);
	$url = isset($links['next']) ? $links['next'] : null;
} while ($url);

