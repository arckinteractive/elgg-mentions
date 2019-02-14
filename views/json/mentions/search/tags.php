<?php

$query = get_input('query');
$query = sanitise_string($query);

$tag_names = elgg_get_registered_tag_metadata_names();

$options = [
	'limit' => 20,
];

if ($query) {
	$options['wheres'][] = "msv.string LIKE '%$query%'";
}

$items = elgg_get_tags($options);

$response = [];

if ($items) {
	foreach ($items as $item) {
		$response[] = [
			'name' => $item->tag,
			'url' => elgg_http_add_url_query_elements('search', [
				'q' => $item->tag,
				'search_type' => 'tags',
			]),
		];
	}
}

elgg_set_http_header('Content-Type: application/json');

echo json_encode($response);