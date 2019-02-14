define(function (require) {

	var elgg = require('elgg');

	var $ = require('jquery');
	require('caret');
	require('atwho');

	var Ajax = require('elgg/Ajax');

	require('elgg/ready');

	var emoji = require('mentions/emoji');

	var atWhoFilter = function (query, callback) {
		var ajax = new Ajax();
		ajax.view('mentions/search/entities', {
			data: {
				query: query,
				view: 'json'
			}
		}).done(callback);
	};

	var hashtagFilter = function (query, callback) {
		var ajax = new Ajax();
		ajax.view('mentions/search/tags', {
			data: {
				query: query,
				view: 'json'
			}
		}).done(callback);
	};

	var config = {
		'@': {
			at: '@',
			headerTpl: null,
			displayTpl: '<li class="mentions-picker-item"><span rel="icon" style="background-image: url(${icon})"></span><span>${name}</span></li>',
			insertTpl: '${atwho-at}[${guid}:${name}]',
			callbacks: {
				remoteFilter: atWhoFilter
			},
			limit: 20
		},
		'#': {
			at: '#',
			headerTpl: null,
			displayTpl: '<li class="mentions-picker-item"><span rel="hashtag">#</span><span>${name}</span></li>',
			insertTpl: '${atwho-at}${name}',
			callbacks: {
				remoteFilter: hashtagFilter
			},
			limit: 20
		},
		':': {
			at: ':',
			headerTpl: null,
			displayTpl: '<li class="mentions-picker-item"><span rel="emoji">${char}</span><span>${name}</span></li>',
			insertTpl: '${char}',
			limit: 20,
			data: emoji
		}
	};

	config = elgg.trigger_hook('mentions', 'config', {}, config);
	
	var initCke = function () {
		elgg.register_hook_handler('prepare', 'ckeditor', function (hook, type, params, CKEDITOR) {
			CKEDITOR.on('instanceReady', function (event) {
				var editor = event.editor;

				function init(editor) {
					if (editor.mode !== 'source') {
						editor.document.getBody().$.contentEditable = true;
						$(editor.document.getBody().$)
							//.atwho('setIframe', editor.window.getFrame().$)
							.atwho(config['@'])
							.atwho(config['#'])
							.atwho(config[':']);
					} else {
						$(editor.container.$).find(".cke_source")
							.atwho(config['@'])
							.atwho(config['#'])
							.atwho(config[':']);
					}

				}

				editor.on('mode', init);
				init(editor);

			});

			return CKEDITOR;
		});

		initCke = elgg.nullFunction;
	};

	return function (selectors) {
		$(selectors)
			.atwho(config['@'])
			.atwho(config['#'])
			.atwho(config[':']);

		initCke();
	};
});
