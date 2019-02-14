<?php

$query = get_input('query');

$items = elgg_trigger_plugin_hook('mentions:search', 'entities', [
	'query' => $query,
], null);

if (!isset($items)) {
	$users = elgg_trigger_plugin_hook('search', 'user', [
		'query' => $query,
		'limit' => 10,
	], []);

	$groups = elgg_trigger_plugin_hook('search', 'group', [
		'query' => $query,
		'limit' => 10,
	], []);

	$items = array_merge($users['entities'], $groups['entities']);
	/* @var $items ElggEntity[] */
}

$response = [];
foreach ($items as $item) {
	$response[] = [
		'name' => $item->getDisplayName(),
		'icon' => $item->getIconURL(['size' => 'tiny']),
		'guid' => $item->guid,
		'url' => $item->getURL(),
	];
}

elgg_set_http_header('Content-Type: application/json');

echo json_encode($response);