<?php

namespace FaimMedia\I18nJson\Compare;

use FaimMedia\I18nJson\Compare\{
	ErrorEnum,
	Exception,
};
use FaimMedia\I18nJson\Output\{
	Color,
	ColorEnum,
};

/**
 * Error class
 */
class Error
{
	/**
	 * Constructor
	 */
	public function __construct(
		protected string $key,
		protected ErrorEnum $type,
		protected ?string $fileName = null,
		protected ?string $language = null,
	)
	{
	}

	/**
	 * Set language
	 */
	public function setLanguage(string $language): void
	{
		$this->language = $language;
	}

	/**
	 * Set file
	 */
	public function setFileName(string $fileName): void
	{
		$this->fileName = $fileName;
	}

	/**
	 * Get type
	 */
	public function getType(): ErrorEnum
	{
		return $this->type;
	}

	/**
	 * Get key
	 */
	public function getKey(): string
	{
		return $this->key;
	}

	/**
	 * Get message
	 */
	public function getMessage(bool $color = true): string
	{
		$color = ColorEnum::RED;
		$message = "Missing key '" . $this->key . "'";

		switch ($this->getType()) {
			case ErrorEnum::FILE:
				$message = 'This file does not exist';
				break;
			case ErrorEnum::FILE_OBSOLETE:
				$color = ColorEnum::YELLOW;
				$message = 'This file is obsolete';
				break;
			case ErrorEnum::JSON:
				$message = 'Could not decode json file';
				break;
			case ErrorEnum::KEY_ARRAY:
				$message = "Invalid array type for key '" . $this->key . "'";
				break;
			case ErrorEnum::KEY_OBSOLETE:
				$color = ColorEnum::YELLOW;
				$message = "Obsolete key '" . $this->key . "'";
				break;
		}

		if ($color) {
			$message = Color::parse($message, $color);
		}

		return $message;
	}

	/**
	 * Get file
	 */
	public function getFileName(): ?string
	{
		return $this->fileName;
	}

	/**
	 * Get language
	 */
	public function getLanguage(): ?string
	{
		return $this->language;
	}

	/**
	 * Get full file
	 */
	public function getFile(): ?string
	{
		return $this->getLanguage() . '/' . $this->getFileName();
	}

	/**
	 * To exception
	 */
	public function toException(): Exception
	{
		$e = new Exception(
			$this->message,
			$this->type->value,
		);
		$e->setError($this);

		return $e;
	}

	/**
	 * Throw exception
	 */
	public function throwException(): void
	{
		throw $this->toException();
	}
}
