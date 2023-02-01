<?php

namespace adamcameron\php8\tests\Fixtures\GetAddress;

class TestConstants
{
    // provided by https://documentation.getaddress.io/ (these do not impact look-up usage)
    public const POSTCODE_OK = "XX2 00X";
    public const POSTCODE_INVALID = "XX4 00X";
    public const POSTCODE_UNAUTHORIZED = "XX4 01X";
    public const POSTCODE_FORBIDDEN = "XX4 03X";
    public const POSTCODE_OVER_LIMIT = "XX4 29X";
    public const POSTCODE_SERVER_ERROR = "XX5 00X";
}
