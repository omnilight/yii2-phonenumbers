<?php

namespace omnilight\phonenumbers;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use yii\validators\Validator;


/**
 * Class PhoneNumberValidator
 * @package \omnilight\phonenumbers
 */
class PhoneNumberValidator extends Validator
{
    /**
     * @var string country code that is used for the phone numbers
     */
    public $defaultRegion;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = \Yii::t('omnilight/phonenumbers', 'Phone number is not valid');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        try {
            $numberProto = self::phoneUtil()->parse($value, $this->defaultRegion);
        } catch (NumberParseException $e) {
            return [$this->message, []];
        }

        if (self::phoneUtil()->isValidNumberForRegion($numberProto, $this->defaultRegion) == false)
            return [$this->message, []];
        return null;
    }

    /**
     * @return PhoneNumberUtil
     */
    protected function phoneUtil()
    {
        return PhoneNumberUtil::getInstance();
    }
} 