<?php

/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/general.php
 */


return array(
	'*' => array(
        'maxUploadFileSize' => 104857600,
        'omitScriptNameInUrls' => true,
        'usePathInfo' => true,
        'currentLgg' => array(
                'en_us' => 'English',
                'es'    => 'Español',
                'zh'    => '中文'
            ),
	),

	'public' => array(
		'siteUrl' => array(
			'en_us' => 'https://pilot.oaklandca.gov/',
			'es' => 'https://pilot.oaklandca.gov/es/',
			'zh' => 'https://pilot.oaklandca.gov/zh/'
		),
		'rootUrl' => 'https://pilot.oaklandca.gov/'
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
		'devMode' => true,
		'siteUrl' => array(
			'en_us' => 'http://localhost:8888/oakland-beta/public/',
			'es' => 'http://localhost:8888/oakland-beta/public/es/',
			'zh' => 'http://localhost:8888/oakland-beta/public/zh/'
		),
		'rootUrl' => 'http://localhost:8888/oakland-beta/public/'
	)
	
);
