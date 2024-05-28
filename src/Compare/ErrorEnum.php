<?php

namespace FaimMedia\I18nJson\Compare;

/**
 * Error enum
 */
enum ErrorEnum: int
{
    case FILE = -1;
    case FILE_OBSOLETE = -2;
    case DIRECTORY = -3;
    case DIRECTORY_OBSOLETE = -4;
    case JSON = -5;
    case KEY = -6;
    case KEY_OBSOLETE = -7;
    case KEY_ARRAY = -8;
}
