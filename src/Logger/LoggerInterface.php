<?php

declare(strict_types = 1);

namespace FaimMedia\I18nJson\Logger;

use FaimMedia\I18nJson\Logger\ColorEnum;

/**
 * Logger interface
 */
interface LoggerInterface
{
	/**
	 * Output message
	 */
	public function output(
		string $message,
		bool $previousLine = false,
		ColorEnum $color = null,
	): void;
}
