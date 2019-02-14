<?php

namespace hypeJunction\Mentions;

class Hooks {

	/**
	 * Links mentions and hashtags
	 *
	 * @param string $value  text to process
	 * @param array  $option Linking options
	 *
	 * @return string
	 */
	public function link($value, array $options = []) {

		//$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

		if (elgg_extract('parse_mentions', $options, true)) {
			// Link mentions @[guid:name]
			$value = preg_replace_callback('/@\[(\d+):(.*?)\]/i', [$this, 'linkMentions'], $value);
			$value = preg_replace_callback('/<a[^>]*?>.*?<\/a>|<.*?>|(^|\s|\!|\.|\?|>|\G)+(@[\p{L}\p{Nd}._-]+)/i', [
				$this,
				'linkUsernames'
			], $value);
		}

		if (elgg_extract('parse_hashtags', $options, true)) {
			// Link hashtags
			$regex = '/<a[^>]*?>.*?<\/a>|<.*?>|(^|\s|\!|\.|\?|>|\G)+(#\w+)/i';
			$value = preg_replace_callback($regex, [$this, 'linkHashtags'], $value);
		}

		return $value;
	}

	/**
	 * Replace callback
	 *
	 * @param array $matches Matches
	 *
	 * @return string
	 */
	public function linkMentions($matches) {
		$entity = get_entity($matches[1]);
		if (!$entity) {
			return elgg_format_element('span', [
				'rel' => 'mention',
				'data-guid' => $matches[1],
			], $matches[2]);
		}

		$icon = elgg_format_element('img', [
			'src' => $entity->getIconURL('tiny'),
			'class' => 'mentions-user-icon',
		]);

		return elgg_view('output/url', [
			'text' => $icon . $matches[2],
			'href' => $entity->getURL(),
			'rel' => 'mention',
			'data-guid' => $matches[1],
			'class' => 'mentions-user-link',
		]);
	}

	/**
	 * Replace callback
	 *
	 * @param array $matches Matches
	 *
	 * @return string
	 */
	public function linkUsernames($matches) {
		if (empty($matches[2])) {
			return $matches[0];
		}

		$username = str_replace('@', '', $matches[2]);
		$entity = get_user_by_username($username);

		if (!$entity) {
			return $matches[0];
		}

		$icon = elgg_format_element('img', [
			'src' => $entity->getIconURL('tiny'),
			'class' => 'mentions-user-icon',
		]);

		return elgg_view('output/url', [
			'text' => $icon . $entity->getDisplayName(),
			'href' => $entity->getURL(),
			'rel' => 'mention',
			'data-guid' => $entity->guid,
			'class' => 'mentions-user-link',
		]);
	}

	/**
	 * Callback function for hashtag preg_replace_callback
	 *
	 * @param array $matches An array of matches
	 *
	 * @return string
	 */

	public static function linkHashtags($matches) {

		if (empty($matches[2])) {
			return $matches[0];
		}

		$tag = str_replace('#', '', $matches[2]);
		$href = elgg_http_add_url_query_elements('search', [
			'q' => $tag,
			'search_type' => 'tags',
		]);

		return $matches[1] . elgg_view('output/url', [
				'rel' => 'hashtag',
				'href' => $href,
				'text' => $matches[2],
			]);
	}

	public static function saveMentions($event, $type, $entity) {

		if (!$entity instanceof \ElggObject) {
			return;
		}

		$description = $entity->description;

		if (!$description) {
			return;
		}

		$poster = $entity->getOwnerEntity();

		preg_match_all('/@\[(\d+):(.*?)\]/i', $description, $matches);

		foreach ($matches as $match) {
			$guid = elgg_extract(1, $match);
			$mention = get_entity($guid);

			if (!$mention) {
				continue;
			}

			add_entity_relationship($entity->guid, 'mentions', $mention->guid);

			if ($mention instanceof \ElggUser && $poster && has_access_to_entity($entity, $mention)) {
				$title = elgg_echo('notify:mention:subject', [$poster->getDisplayName()]);
				$description = elgg_echo('notify:mention:body', [
					$poster->getDisplayName(),
					$entity->getURL(),
				]);

				notify_user($mention->guid, $entity->owner_guid, $title, $description, [
					'action' => 'mention',
					'object' => $entity,
					'subject' => $poster,
					'url' => $entity->getURL(),
				]);
			}
		}
	}

	public static function expandMentions($hook, $type, $value) {
		$options = [
			'sanitize' => false,
			'autop' => false,
			'parse_urls' => true,
			'parse_emails' => true,
			'parse_usernames' => true,
			'parse_hashtags' => true,
			'parse_mentions' => true,
		];

		return elgg_format_html($value, $options);
	}

	public static function prepareHtmlOutput($hook, $type, $value) {
		$html = elgg_extract('html', $value);
		$options = elgg_extract('options', $value);

		if (!isset($options['parse_hashtags'])) {
			$options['parse_hashtags'] = true;
		}

		if (!isset($options['parse_mentions'])) {
			$options['parse_mentions'] = true;
		}

		$svc = new Hooks();

		$html = $svc->link($html, $options);

		$options['parse_hashtags'] = false;
		$options['parse_mentions'] = false;

		return [
			'html' => $html,
			'options' => $options,
		];
	}
}
