<?php

$plugin_root = __DIR__;
$root = dirname(dirname($plugin_root));
$alt_root = dirname(dirname(dirname($root)));

if (file_exists("$plugin_root/vendor/autoload.php")) {
	$views_path = $plugin_root;
} else if (file_exists("$root/vendor/autoload.php")) {
	$views_path = $root;
} else {
	$views_path = $alt_root;
}

return [
	'default' => [
		'atwho.js' => $views_path . '/vendor/npm-asset/at.js/dist/js/jquery.atwho.min.js',
		'atwho.css' => $views_path . '/vendor/npm-asset/at.js/dist/css/jquery.atwho.min.css',
		'caret.js' => $views_path . '/vendor/npm-asset/jquery.caret/dist/jquery.caret.min.js',
	],
];
