<?php

use FaimMedia\I18nJson\Logger\{
	Color,
	ColorEnum,
};

define('ROOT_PATH', realpath(__DIR__ . '/..') . '/');
define('SOURCE_PATH', ROOT_PATH . 'src/');
define('TEST_PATH', realpath(__DIR__) . '/');

require ROOT_PATH . 'vendor/autoload.php';
