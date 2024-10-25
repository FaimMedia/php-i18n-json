<?php

namespace FaimMedia\I18nJson\Compare;

use FaimMedia\I18nJson\Compare\Error;
use FaimMedia\I18nJson\Exception as BaseException;

/**
 * Compare Exception
 */
class Exception extends BaseException
{
    protected Error $error;

    /**
     * Path is invalid
     */
    public const PATH = -1;

    /**
     * Base language does not exist
     */
    public const BASE_LANGUAGE = -2;

    /**
     * File does not exist
     */
    public const MISSING_FILE = -3;

    /**
     * File could not be parsed
     */
    public const JSON_PARSE = -4;

    /**
     * File could not be parsed
     */
    public const INVALID_IGNORE = -5;

    /**
     * Set compare error
     */
    public function setError(Error $error): void
    {
        $this->error = $error;
    }

    /**
     * Set compare error
     */
    public function getError(): Error
    {
        return $this->error;
    }
}
