<?php

declare(strict_types = 1);

namespace FaimMedia\I18nJson;

use FaimMedia\I18nJson\Compare\{
	Exception,
	Error,
	ErrorEnum,
};

/**
 * Translation compare class
 */
class Compare
{
	protected string $path;
	protected array $languages;
	protected array $ignoreLanguages;
	protected array $files;
	protected string $baseLanguage;

	/**
	 * @property Error[] $errors
	 */
	protected array $errors = [];

	/**
	 * Constructor
	 */
	public function __construct(array $options)
	{
		if (!isset($options['path'])) {
			throw new Exception('Path option is missing', Exception::PATH);
		}

		$this->setPath($options['path']);

		if (!isset($options['baseLanguage'])) {
			throw new Exception('Base language option is missing', Exception::BASE_LANGUAGE);
		}

		$this->ignoreLanguages = $options['ignoreLanguages'] ?? [];
		if (in_array($options['baseLanguage'], $this->ignoreLanguages)) {
			throw new Exception('Cannot ignore the base language', Exception::INVALID_IGNORE);
		}

		$this->setBaseLanguage($options['baseLanguage']);
		$this->collectLanguages();

		if (!$this->files) {
			throw new Exception('Base language folder is empty', Exception::BASE_LANGUAGE);
		}
	}

	/**
	 * Set path
	 */
	public function setBaseLanguage(string $baseLanguage): void
	{
		$this->baseLanguage = $baseLanguage;
	}

	/**
	 * Set base language
	 */
	public function setPath(string $path): void
	{
		if (!file_exists($path) || !is_dir($path)) {
			throw new Exception(
				'The path `' . $path . '` does not exist or is not a directory',
				Exception::PATH,
			);
		}

		$this->path = rtrim($path, '/') . '/';
	}

	/**
	 * Collect languages
	 */
	protected function collectLanguages(): array
	{
		if (isset($this->languages)) {
			return $this->languages;
		}

		$files = glob($this->path . '*');

		$languages = [];
		foreach ($files as $file) {
			if (!is_dir($file)) {
				continue;
			}

			$baseName = basename($file);

			/**
			 * Check if language should be ignored
			 */
			if (in_array($baseName, $this->ignoreLanguages)) {
				continue;
			}

			$languages[$baseName] = $this->collectFiles($file . '/');
		}

		if (!array_key_exists($this->baseLanguage, $languages)) {
			throw new Exception('Base language folder does not exist', Exception::BASE_LANGUAGE);
		}

		$this->languages = $languages;
		$this->files = $this->languages[$this->baseLanguage];

		return $this->languages;
	}

	/**
	 * Collect files
	 */
	protected function collectFiles(string $path, ?int $offset = null): array
	{
		$files = glob($path . '*', GLOB_MARK);

		if ($offset === null) {
			$offset = strlen($path);
		}

		$array = [];
		foreach ($files as $file) {
			if (is_dir($file)) {
				$array = [
					...$array,
					...$this->collectFiles($file, $offset),
				];
				continue;
			}

			if (substr($file, -5) !== '.json') {
				continue;
			}

			$array[] = substr($file, $offset);
		}

		return $array;
	}

	/**
	 * Run with transaction
	 */
	public function run(): void
	{
		try {
			$this->runCompare();
		} catch (Exception $e) {
			echo $e->getMessage() . PHP_EOL;
		}
	}

	/**
	 * Run compare
	 */
	protected function runCompare(): void
	{
		/**
		 * Compare file existance and obsolete
		 */
		$base = $this->languages[$this->baseLanguage];
		foreach ($this->languages as $language => $files) {
			if ($language === $this->baseLanguage) {
				continue;
			}

			foreach (array_diff($base, $files) as $file) {
				$this->errors[] = new Error(
					'0',
					ErrorEnum::FILE,
					$file,
					$language,
				);
			}

			foreach (array_diff($files, $base) as $file) {
				$this->errors[] = new Error(
					'0',
					ErrorEnum::FILE_OBSOLETE,
					$file,
					$language,
				);
			}
		}


		foreach ($this->languages as $language => $files) {
			if ($language === $this->baseLanguage) {
				continue;
			}

			foreach ($this->files as $file) {
				$fullPath = $this->path . $language . '/' . $file;

				if (!file_exists($fullPath)) {
					continue;
				}

				try {
					$errors = $this->compareFiles(
						$this->path . $this->baseLanguage . '/' . $file,
						$fullPath,
					);

					foreach ($errors as $error) {
						$this->errors[] = new Error(
							$error[0],
							$error[1],
							$file,
							$language,
						);
					}
				} catch (Exception $e) {
					$error = $e->getError();
					$error->setLanguage($language);
					$error->setFileName($file);

					$this->errors[] = $error;
				}
			}
		}
	}

	/**
	 * Print errors
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * Sort errors
	 */
	public function sortErrors(): void
	{
		//@todo
	}

	/**
	 * Compare file
	 *
	 * @property string $file1 Base line json file
	 * @property string $file2 The file that should be checked
	 */
	protected function compareFiles(string $file1, string $file2): array
	{
		$json1 = $this->readJson($file1);
		$json2 = $this->readJson($file2);

		return $this->compareJson($json1, $json2);
	}

	/**
	 * Compare json
	 */
	protected function compareJson(array $a, array $b, string $prefix = null): array
	{
		$errors = [];

		/**
		 * Diff
		 */
		$diff = array_diff_key($a, $b);

		foreach (array_keys($diff) as $key) {
			$errors[] = [$prefix . $key, ErrorEnum::KEY];
		}

		/**
		 * Obsolete
		 */
		$obsolete = array_diff_key($b, $a);

		foreach (array_keys($obsolete) as $key) {
			$errors[] = [$prefix . $key, ErrorEnum::KEY_OBSOLETE];
		}

		/**
		 * Recursive check
		 */
		foreach ($a as $key => $value) {
			if (!is_array($value)) {
				continue;
			}

			if (!is_array($b[$key])) {
				$errors[] = [$prefix . $key, ErrorEnum::KEY_ARRAY];
				continue;
			}

			$errors = array_merge(
				$errors,
				$this->compareJson($value, $b[$key], $prefix . $key . '.'),
			);
		}

		return $errors;
	}

	/**
	 * Read json
	 */
	protected function readJson(string $file): array
	{
		$content = file_get_contents($file);

		$json = json_decode($content, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			(new Error(
				'0',
				ErrorEnum::JSON,
			))->throwException();
		}

		return $json;
	}
}
