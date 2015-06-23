<?php

namespace omnilight\phonenumbers;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;


/**
 * Class PhoneNumber
 */
class PhoneNumber
{
    /**
     * Formats number to the desired format
     * @param $phone
     * @param int $format
     * @param string $region
     * @return string
     */
    public static function format($phone, $format = PhoneNumberFormat::E164, $region = 'RU')
    {
        return self::phoneUtil()->format(self::phoneUtil()->parse($phone, $region), $format);
    }

    /**
     * @return PhoneNumberUtil
     */
    public static function phoneUtil()
    {
        return PhoneNumberUtil::getInstance();
    }

    /**
     * Validates number
     * @param $phone
     * @param $region
     * @return bool
     */
    public static function validate($phone, $region = 'RU')
    {
        try {
            return self::phoneUtil()->isValidNumberForRegion(self::phoneUtil()->parse($phone, $region), $region);
        } catch (NumberParseException $e) {
            return false;
        }
    }
}