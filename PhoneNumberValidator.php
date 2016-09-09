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
     * @const Simple validation of the phone number by isValid
     */
    const VALID = 1;

    /**
     * @const Validation for the specified region [[defaultRegion]]
     */
    const VALID_FOR_REG = 2;
    

    /**
     * @var Type of validation: simple isValid or validation for region specified through [[defaultRegion]].
     * The default is [[self::VALID]] (simple validation)
     */
    public $validationType = self::VALID;
    
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
        if (trim($value) == '')
            return null;
        try {
            $numberProto = self::phoneUtil()->parse($value, $this->defaultRegion);
        } catch (NumberParseException $e) {
            return [$this->message, []];
        }

        if ($this->validationType === static::VALID &&
                self::phoneUtil()->isValidNumber($numberProto, $this->defaultRegion) == false)
            return [$this->message, []];

        if ($this->validationType === static::VALID_FOR_REG &&
                self::phoneUtil()->isValidNumberForRegion($numberProto, $this->defaultRegion) == false)
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
