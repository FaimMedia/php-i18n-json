<?php

namespace FaimMedia\I18nJson\Test\Validate;

use FaimMedia\I18nJson\Test\AbstractTestCase;

use FaimMedia\I18nJson\Compare;
use FaimMedia\I18nJson\Compare\{
	Error,
	ErrorEnum,
};

/**
 * Compare tests
 */
class CompareTest extends AbstractTestCase
{
	/**
	 * Init compare
	 */
	protected function initCompare(string $path): void
	{
		$this->compare = new Compare([
			'path'         => $path,
			'baseLanguage' => 'nl',
		]);
	}

	/**
	 * Test missing keys
	 */
	public function testMissingKeys(): void
	{
		$this->initCompare(TEST_PATH . 'json/compare1/');
		$this->compare->run();

		$errors = $this->compare->getErrors();

		parent::assertCount(2, $errors);

		parent::assertError($errors, function(Error $error) {
			parent::assertSame('textNew', $error->getKey());
			parent::assertSame(ErrorEnum::KEY, $error->getType());
			parent::assertSame('common.json', $error->getFileName());
			parent::assertSame('en/common.json', $error->getFile());
			parent::assertSame('en', $error->getLanguage());
		});

		parent::assertError($errors, function(Error $error) {
			parent::assertSame('textNew', $error->getKey());
			parent::assertSame(ErrorEnum::KEY, $error->getType());
			parent::assertSame('common.json', $error->getFileName());
			parent::assertSame('fr/common.json', $error->getFile());
			parent::assertSame('fr', $error->getLanguage());
		});
	}

	/**
	 * Test missing keys
	 */
	public function testMissingAndObsoleteFiles(): void
	{
		$this->initCompare(TEST_PATH . 'json/compare2/');
		$this->compare->run();

		$errors = $this->compare->getErrors();

		parent::assertCount(3, $errors);

		parent::assertError($errors, function(Error $error) {
			parent::assertSame(ErrorEnum::FILE, $error->getType());
			parent::assertSame('missing.json', $error->getFileName());
			parent::assertSame('en/missing.json', $error->getFile());
			parent::assertSame('en', $error->getLanguage());
		}, 'file');

		parent::assertError($errors, function(Error $error) {
			parent::assertSame(ErrorEnum::FILE_OBSOLETE, $error->getType());
			parent::assertSame('obsolete.json', $error->getFileName());
			parent::assertSame('en/obsolete.json', $error->getFile());
			parent::assertSame('en', $error->getLanguage());
		}, 'file-obsolete-en');

		parent::assertError($errors, function(Error $error) {
			parent::assertSame('textFooter', $error->getKey());
			parent::assertSame(ErrorEnum::KEY_OBSOLETE, $error->getType());
			parent::assertSame('test.json', $error->getFileName());
			parent::assertSame('en/test.json', $error->getFile());
			parent::assertSame('en', $error->getLanguage());
		}, 'key-obsolete-en');
	}

	/**
	 * Test object arrays
	 */
	public function testObjectArrayKeys(): void
	{
		$this->initCompare(TEST_PATH . 'json/compare3/');
		$this->compare->run();

		$errors = $this->compare->getErrors();

		parent::assertCount(5, $errors);

		parent::assertError($errors, function(Error $error) {
			parent::assertSame('nestedObject.0.3', $error->getKey());
			parent::assertSame(ErrorEnum::KEY, $error->getType());
			parent::assertSame('common.json', $error->getFileName());
			parent::assertSame('de/common.json', $error->getFile());
			parent::assertSame('de', $error->getLanguage());
		}, 'missing-key-de');

		parent::assertError($errors, function(Error $error) {
			parent::assertSame('nestedObject.0.1', $error->getKey());
			parent::assertSame(ErrorEnum::KEY_OBSOLETE, $error->getType());
			parent::assertSame('common.json', $error->getFileName());
			parent::assertSame('de/common.json', $error->getFile());
			parent::assertSame('de', $error->getLanguage());
		}, 'key-obsolete-de-1');

		parent::assertError($errors, function(Error $error) {
			parent::assertSame('nestedObject.customKey', $error->getKey());
			parent::assertSame(ErrorEnum::KEY_OBSOLETE, $error->getType());
			parent::assertSame('common.json', $error->getFileName());
			parent::assertSame('de/common.json', $error->getFile());
			parent::assertSame('de', $error->getLanguage());
		}, 'key-obsolete-de-2');

		parent::assertError($errors, function(Error $error) {
			parent::assertSame('nestedObject.subItem', $error->getKey());
			parent::assertSame(ErrorEnum::KEY_OBSOLETE, $error->getType());
			parent::assertSame('common.json', $error->getFileName());
			parent::assertSame('de/common.json', $error->getFile());
			parent::assertSame('de', $error->getLanguage());
		}, 'key-obsolete-de-3');

		parent::assertError($errors, function(Error $error) {
			parent::assertSame('textHomepage', $error->getKey());
			parent::assertSame(ErrorEnum::KEY_ARRAY, $error->getType());
			parent::assertSame('common.json', $error->getFileName());
			parent::assertSame('de/common.json', $error->getFile());
			parent::assertSame('de', $error->getLanguage());
		}, 'key-invalid-type');
	}
}
