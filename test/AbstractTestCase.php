<?php

namespace FaimMedia\I18nJson\Test;

use FaimMedia\I18nJson\Compare;
use FaimMedia\I18nJson\Compare\Error;

use PHPUnit\Framework\TestCase;

use Closure;

use PHPUnit\Framework\ExpectationFailedException;

/**
 * Abstract Test Case class
 */
abstract class AbstractTestCase extends TestCase
{
	protected Compare $compare;

	/**
	 * Setup
	 */
	public function setUp(): void
	{
		register_shutdown_function(function(): void {
			$this->tearDown();
		});
	}

	/**
	 * Assert error
	 */
	public function assertError(
		array $errors,
		Closure $callback,
		?string $id = null,
	): void
	{
		$complete = false;
		foreach ($errors as $error) {
			try {
				parent::assertInstanceOf(Error::class, $error);

				$callback($error);
			} catch (ExpectationFailedException $e) {
				continue;
			}

			$complete = true;
		}

		$message = 'Error list does not contain expected error';
		if ($message) {
			$message .= ': ' . $id;
		}

		parent::assertTrue($complete, $message);
	}

	/**
	 * Teardown
	 */
	public function tearDown(): void
	{

	}
}
