<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 10/13/18
 * Time: 8:34 PM
 */

namespace App\Common;


class AppExceptionCodes
{
    const
    INVALID_POSITION = 0010,
    UNHANDLED_MESSAGE = 0020,
    UNHANDLED_CONDITION=0030,
    MISSING_FILE=0040,
    FOUND_BUT_EXPECTED = 1000,
    INVALID_PARAMETER = 1010,
    UNRECOGNIZED_VALUE = 1020,
    MISSING_KEYS = 1030,
    INVALID_RANGE = 1040,
    OVERLAPPING_RANGE = 1050,
    ARRAY_EXPECTED = 1060,
    INDEXED_ARRAY_EXPECTED = 1065,
    SCALER_EXPECTED = 1070,
    EMPTY_ARRAY_EXPECTED = 1080,
    PARTNER_VALUES = 1090,
    FILE_NOT_FOUND = 2000,
    BAD_INDEX = 2010,
    EXPECTED_STRUCTURE = 2020,
    BAD_DATE_ORDER = 2030,
    INVALID_EMAIL = 2040,
    PARAMETER_MISSING = 2050;
}