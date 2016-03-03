<?php

/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/general.php
 */


return array(
	'*' => array(
		'devMode' => true,
        'maxUploadFileSize' => 104857600,
        'omitScriptNameInUrls' => true
	),

	'live' => array(
		'siteUrl' => array(
			'en_us' => 'https://beta.oaklandca.gov/',
			'es' => 'https://beta.oaklandca.gov/es/',
			'zh' => 'https://beta.oaklandca.gov/zh/'
		),
		'rootUrl' => 'https://beta.oaklandca.gov/'
	),

	'dev' => array(
		'siteUrl' => array(
			'en_us' => 'http://localhost:8888/oakland-beta/public/',
			'es' => 'http://localhost:8888/oakland-beta/public/es/',
			'zh' => 'http://localhost:8888/oakland-beta/public/zh/'
		),
		'rootUrl' => 'http://localhost:8888/oakland-beta/public/'
	)
	
);
