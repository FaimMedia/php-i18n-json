<?php

declare(strict_types = 1);

namespace FaimMedia\I18nJson\Output;

use FaimMedia\I18nJson\Output\ColorEnum;

/**
 * Color output logger
 */
class Color
{
	protected static string $prevMessage;

	/**
	 * Output message
	 */
	public static function parse(
		string $message,
		ColorEnum $color = null,
		bool $bold = false,
		bool $previousLine = false,
	): string
	{
		$output = '';

		if ($previousLine) {
			$output .= chr(27) . "[1A";
			$output .= self::$prevMessage . ' ';
		}

		if ($color) {
			$message = chr(27) . "[" . $color->value . ($bold ? ';1' : '') . "m" . $message  . chr(27) . "[0m";
		}

		if (!$previousLine) {
			self::$prevMessage = $message;
		}

		$output .= $message;

		return $output;
	}
}
