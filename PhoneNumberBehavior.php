<?php

namespace omnilight\phonenumbers;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\BaseActiveRecord;


/**
 * Class PhoneNumberBehavior
 * @package \omnilight\phonenumbers
 */
class PhoneNumberBehavior extends Behavior
{
    /**
     * @var array attributes list that should have transformation into local format of the phone number:
     * ~~~
     * ['phoneLocal' => 'dbPhone']
     * ~~~
     */
    public $attributes;
    /**
     * @var bool whether to validate attributes automatically on model's onValidate event
     */
    public $performValidation = true;
    /**
     * @var string country code that is used for the phone numbers
     */
    public $defaultRegion;
    /**
     * @var int phone number format used in the original attribute
     */
    public $attributeFormat = PhoneNumberFormat::E164;
    /**
     * @var int phone number format used for local attribute
     */
    public $localFormat = PhoneNumberFormat::INTERNATIONAL;

    /**
     * @var array
     */
    protected $_localValues;

    public function events()
    {
        $events = [];
        if ($this->performValidation) {
            $events[BaseActiveRecord::EVENT_BEFORE_VALIDATE] = 'onBeforeValidate';
        }
        return $events;
    }

    /**
     * Performs validation for all the attributes
     * @param Event $event
     */
    public function onBeforeValidate($event)
    {
        $validator = new PhoneNumberValidator();
        foreach ($this->attributes as $localAttribute => $dbAttribute) {
            $validator->validateAttribute($this->owner, $localAttribute);
        }
    }

    public function __get($name)
    {
        if ($this->hasLocalValue($name)) {
            return $this->getLocalValue($name);
        } else {
            return parent::__get($name);
        }
    }

    public function __set($name, $value)
    {
        if ($this->hasLocalValue($name)) {
            $this->setLocalValue($name, $value);
        } else {
            parent::__set($name, $value);
        }
    }

    protected function hasLocalValue($attributeLocal)
    {
        return isset($this->attributes[$attributeLocal]);
    }

    /**
     * @param string $attributeLocal
     * @return string
     */
    public function getLocalValue($attributeLocal)
    {
        if (isset($this->_localValues[$attributeLocal])) {
            return $this->_localValues[$attributeLocal];
        } else {
            $attributeValue = $this->owner->{$this->attributes[$attributeLocal]};
            if (trim($attributeValue) == '')
                return '';

            try {
                $numberProto = self::phoneUtil()->parse($attributeValue, $this->defaultRegion);
            } catch (NumberParseException $e) {
                \Yii::error('Can not parse DB phone number ' . $attributeValue, self::className());
                return $attributeValue;
            }

            return self::phoneUtil()->format($numberProto, $this->localFormat);
        }
    }

    /**
     * @return PhoneNumberUtil
     */
    protected static function phoneUtil()
    {
        return PhoneNumberUtil::getInstance();
    }

    /**
     * @param string $attributeLocal
     * @param string $value
     */
    protected function setLocalValue($attributeLocal, $value)
    {
        $this->_localValues[$attributeLocal] = $value;
        try {
            $numberProto = self::phoneUtil()->parse($value, $this->defaultRegion);
        } catch (NumberParseException $e) {
            \Yii::error('Can not parse phone number ' . $value, self::className());
            return;
        }

        if (self::phoneUtil()->isValidNumberForRegion($numberProto, $this->defaultRegion)) {
            $this->owner->{$this->attributes[$attributeLocal]} = self::phoneUtil()->format($numberProto, $this->attributeFormat);
        } else {
            return;
        }
    }

    public function canGetProperty($name, $checkVars = true)
    {
        if ($this->hasLocalValue($name))
            return true;
        else
            return parent::canGetProperty($name, $checkVars);
    }

    public function canSetProperty($name, $checkVars = true)
    {
        if ($this->hasLocalValue($name))
            return true;
        else
            return parent::canSetProperty($name, $checkVars);
    }
} 