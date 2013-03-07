<?php

require __DIR__ . '/headers.php';

$url = 'https://api.github.com/users/hubgit/gists'; // edit this username
$dir = __DIR__ . '/../gists'; // output directory

do {
	print $url . "\n";
	$items = json_decode(file_get_contents($url));

	foreach ($items as $item) {
		$id = $item->id;

		if (!preg_match('/^\d+$/', $id)) {
			exit($id . ' is not an integer Gist id');
		}

		$output = $dir . '/' . $id;

		// TODO: updates
		if (file_exists($output)) {
			print $output . " already exists\n";
			//continue;
		}

		$command = sprintf('git clone %s %s', escapeshellarg($item->git_pull_url), escapeshellarg($output));
		print "> " . $command . "\n";
		//exec($command);

		file_put_contents($output . '.json', json_encode($item));
	}

	$links = parseLinkResponseHeaders($http_response_header);
	$url = isset($links['next']) ? $links['next'] : null;
} while ($url);

