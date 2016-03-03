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
			'en' => 'https://beta.oaklandca.gov/',
			'es' => 'https://beta.oaklandca.gov/es/',
			'ch' => 'https://beta.oaklandca.gov/ch/'
		)
	),

	'dev' => array(
		'siteUrl' => array(
			'en' => 'http://localhost:8888/oakland-beta/public/'
		)
	)
	
);
