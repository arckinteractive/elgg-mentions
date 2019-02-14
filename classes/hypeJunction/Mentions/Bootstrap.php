<?php

namespace hypeJunction\Mentions;

use Elgg\PluginBootstrap;

class Bootstrap extends PluginBootstrap {

	/**
	 * Get plugin root
	 * @return string
	 */
	protected function getRoot() {
		return $this->plugin->getPath();
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		elgg_extend_view('elements/forms.css', 'mentions/mentions.css');

		elgg_extend_view('input/text', 'mentions/mentions');
		elgg_extend_view('input/plaintext', 'mentions/mentions');
		elgg_extend_view('input/longtext', 'mentions/mentions');

		elgg_register_simplecache_view('mentions/emoji.js');

		elgg_register_plugin_hook_handler('prepare', 'html', [Hooks::class, 'prepareHtmlOutput']);

		elgg_register_plugin_hook_handler('view', 'output/plaintext', [Hooks::class, 'expandMentions'], 9999);
		elgg_register_plugin_hook_handler('view', 'search/entity', [Hooks::class, 'expandMentions'], 9999);

		elgg_register_event_handler('create', 'object', [Hooks::class, 'saveMentions']);

		elgg_register_ajax_view('mentions/search/entities');
		elgg_register_ajax_view('mentions/search/tags');
	}

	/**
	 * {@inheritdoc}
	 */
	public function ready() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function shutdown() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function upgrade() {

	}

}