<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;

class Country extends AbstractExtensibleObject implements
    \MSP\TwoFactorAuth\Api\Data\CountryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($value)
    {
        $this->setData(self::ID, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($value)
    {
        $this->setData(self::CODE, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($value)
    {
        $this->setData(self::NAME, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDialCode()
    {
        return $this->_get(self::DIAL_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDialCode($value)
    {
        $this->setData(self::DIAL_CODE, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_get(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \MSP\TwoFactorAuth\Api\Data\CountryExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
