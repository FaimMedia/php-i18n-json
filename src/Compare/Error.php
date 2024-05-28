<?php

namespace FaimMedia\I18nJson\Compare;

use FaimMedia\I18nJson\Compare\{
	ErrorEnum,
	Exception,
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
	public function getMessage(): string
	{
		return 'Missing key ' . $this->key;
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
