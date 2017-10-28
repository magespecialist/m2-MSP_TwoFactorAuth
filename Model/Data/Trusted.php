<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;

class Trusted extends AbstractExtensibleObject implements
    \MSP\TwoFactorAuth\Api\Data\TrustedInterface
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
    public function getDateTime()
    {
        return $this->_get(self::DATE_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setDateTime($value)
    {
        $this->setData(self::DATE_TIME, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->_get(self::USER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setUserId($value)
    {
        $this->setData(self::USER_ID, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeviceName()
    {
        return $this->_get(self::DEVICE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setDeviceName($value)
    {
        $this->setData(self::DEVICE_NAME, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->_get(self::TOKEN);
    }

    /**
     * {@inheritdoc}
     */
    public function setToken($value)
    {
        $this->setData(self::TOKEN, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastIp()
    {
        return $this->_get(self::LAST_IP);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastIp($value)
    {
        $this->setData(self::LAST_IP, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserAgent()
    {
        return $this->_get(self::USER_AGENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUserAgent($value)
    {
        $this->setData(self::USER_AGENT, $value);
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
        \MSP\TwoFactorAuth\Api\Data\TrustedExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
