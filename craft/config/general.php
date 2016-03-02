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
        'maxUploadFileSize' => 104857600
	),
	'dev' => array(
		'siteUrl' => array(
			'en' => 'http://localhost:8888/oakland-beta/public/'
		)
	)
	
);
