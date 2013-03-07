<?php

function parseLinkResponseHeaders($headers) {
	$links = array();

	foreach ($headers as $header) {
		list($headerName, $headerContent) = preg_split('/:\s+/', $header, 2);

		switch ($headerName) {
			case 'Link':
				$links = preg_split('/\s*,\s*/', $headerContent);
				foreach ($links as $link) {
					$linkParts = preg_split('/\s*;\s*/', $link);
					if (preg_match('/^<(.+?)>$/', $linkParts[0], $matches)) {
						$linkURL = $matches[1];

						foreach ($linkParts as $linkPart) {
							if (preg_match('/rel="(.+?)"/', $linkPart, $matches)) {
								$linkRelation = $matches[1];
								$links[$linkRelation] = $linkURL;
							}
						}
					}
				}
				break;
		}
	}

	return $links;
}