#!/usr/bin/env php
<?php

use FaimMedia\I18nJson\Compare;

define('ROOT_PATH', realpath(__DIR__ . '/..') . '/');
define('SOURCE_PATH', ROOT_PATH . 'src/');
define('TEST_PATH', realpath(__DIR__) . '/');

require ROOT_PATH . 'vendor/autoload.php';

$type = $argv[1] ?? null;

if (!in_array($type, ['compare', 'format', 'generate'])) {
	echo 'Possible options:' . PHP_EOL;
	echo ' - compare' . PHP_EOL;
	echo '      Compare all translations' . PHP_EOL;
	echo ' - format' . PHP_EOL;
	echo '      Format all files with same sorting as base path' . PHP_EOL;
	echo ' - generate' . PHP_EOL;
	echo '      Generate a new language folder based on the files from languagePath' . PHP_EOL;

	echo PHP_EOL;
	exit(1);
}

$required = [
	'path',
	'baseLanguage',
];

$options = getopt('', [
	'path:',
	'baseLanguage:',
	'debug',
	'sleep::',
]);

$errors = [];
foreach ($required as $field) {
	if (!empty($options[$field])) {
		continue;
	}

	$errors[] = 'Missing required argument --' . $field;
}

if ($errors) {
	echo ' - ' . join(PHP_EOL . ' - ', $errors);
	echo PHP_EOL;
	exit(1);
}

$class = FaimMedia\I18nJson::class . '\\' . ucfirst($type);

try {
	$compare = new $class([
		...$options,
	]);

	$compare->run();
} catch (Throwable $e) {
	$message = 'An error occurred: ' . $e->getMessage();

	$wraps = explode("\n", wordwrap($message, 75));
	$maxLength = max(array_map('strlen', $wraps)) + 4;

	echo chr(27) . '[41m' . str_repeat(' ', $maxLength) . chr(27) . '[0m' . PHP_EOL;

	foreach ($wraps as $wrap) {
		echo chr(27) . '[41m' . '  ' . str_pad($wrap, $maxLength - 2, ' ') . chr(27) . '[0m' . PHP_EOL;
	}

	echo chr(27) . '[41m' . str_repeat(' ', $maxLength) . chr(27) . '[0m' . PHP_EOL;

	if (($options['debug'] ?? null) !== null) {
		echo 'Stack trace: ' . PHP_EOL;

		$trace = $e->getTrace();
		array_unshift($trace, [
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);

		echo json_encode($trace, JSON_PRETTY_PRINT);
	}

	exit(2);
}

exit(0);
