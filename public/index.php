<?php

switch ($_SERVER['HTTP_HOST']) 
{   
    // If the SERVER_NAME variable matches our case, 
    // assign the CRAFT_ENVIRONMENT variable a keyword 
    // that identifies this environment that we can 
    // use in our multi-environment config

    case 'beta.oaklandca.gov' :
        if($_SERVER['HTTP_X_FORWARDED_HOST'] == 'pilot.oaklandca.gov') {
            define('CRAFT_ENVIRONMENT', 'public');
        }
        else {
            define('CRAFT_ENVIRONMENT', 'live');
        }
        break;

    case 'localhost:8888' :
        define('CRAFT_ENVIRONMENT', 'dev');
        break;

    default :
        define('CRAFT_ENVIRONMENT', 'live');
        break;
}

// Path to your craft/ folder
$craftPath = '../craft';

// Tell Craft to serve the English content
define('CRAFT_LOCALE', 'en_us');

// Do not edit below this line
$path = rtrim($craftPath, '/').'/app/index.php';

if (!is_file($path))
{
    if (function_exists('http_response_code'))
    {
        http_response_code(503);
    }

    exit('Could not find your craft/ folder. Please ensure that <strong><code>$craftPath</code></strong> is set correctly in '.__FILE__);
}

require_once $path;
