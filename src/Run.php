#!/usr/bin/env php
<?php

use FaimMedia\I18nJson\Compare;
use FaimMedia\I18nJson\Compare\ErrorEnum;
use FaimMedia\I18nJson\Output\{
	Color,
	ColorEnum,
};

define('ROOT_PATH', realpath(__DIR__ . '/..') . '/');
define('SOURCE_PATH', ROOT_PATH . 'src/');
define('TEST_PATH', realpath(__DIR__) . '/');
define(
	'VENDOR_PATH',
	($_ENV['COMPOSER_RUNTIME_BIN_DIR'] ?? null)
		? realpath($_ENV['COMPOSER_RUNTIME_BIN_DIR'] . '/..') . '/'
		: ROOT_PATH . 'vendor/',
);

require VENDOR_PATH . 'autoload.php';

function outputUsage(): void {
	echo 'Usage:' . PHP_EOL;
	echo '    i18n-json [compare|format|generate] [path] [baseLanguage] [ignoreLanguageCode,ignoreLanguageCode,…]' . PHP_EOL;
	echo PHP_EOL;
};

/**
 * Check type
 */
$type = $argv[1] ?? null;
if (!in_array($type, ['compare', 'format', 'generate'])) {
	echo Color::parse('Invalid option provided', ColorEnum::RED, true) . PHP_EOL;
	outputUsage();

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

$path = $argv[2] ?? null;
if (!$path) {
	echo Color::parse('Invalid path provided', ColorEnum::RED, true) . PHP_EOL;
	outputUsage();
	exit(1);
}

$baseLanguage = $argv[3] ?? null;
if (!$baseLanguage) {
	echo Color::parse('Invalid baseLanguage provided', ColorEnum::RED, true) . PHP_EOL;
	outputUsage();
	exit(1);
}

if (!file_exists($path)) {
	echo Color::parse('The provided path does not exist', ColorEnum::RED, true) . PHP_EOL;
	exit(2);
}

if (!file_exists(rtrim($path, '/') . '/' . $baseLanguage)) {
	echo Color::parse('The provided baseLanguage does not exist', ColorEnum::RED, true) . PHP_EOL;
	exit(2);
}

$options = getopt('', [
	'debug',
	'sleep::',
]);

/**
 * Init
 */
$class = FaimMedia\I18nJson::class . '\\' . ucfirst($type);

try {
	$compare = new $class([
		'path'            => $path,
		'baseLanguage'    => $baseLanguage,
		'ignoreLanguages' => ($argv[4] ?? null)
			? explode(',', $argv[4])
			: [],
	]);

	$compare->run();

	if ($compare instanceof Compare) {
		$compare->sortErrors();

		$errorCount = 0;

		$lastFileName = null;
		$lastLanguage = null;
		foreach ($compare->getErrors() as $error) {
			if (
				$error->getFileName() !== $lastFileName
				|| $error->getLanguage() !== $lastLanguage
			) {
				echo '[' . $error->getLanguage() . '] ' . $error->getFileName() . PHP_EOL;
			}

			echo ' - ' . $error->getMessage() . PHP_EOL;

			if (!in_array($error->getType(), ErrorEnum::getWarnings())) {
				$errorCount++;
			}

			$lastFileName = $error->getFileName();
			$lastLanguage = $error->getLanguage();
		}

		if ($errorCount) {
			exit(1);
		}
	}

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
