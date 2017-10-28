<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;

class UserConfig extends AbstractExtensibleObject implements
    \MSP\TwoFactorAuth\Api\Data\UserConfigInterface
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
    public function getEncodedConfig()
    {
        return $this->_get(self::ENCODED_CONFIG);
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodedConfig($value)
    {
        $this->setData(self::ENCODED_CONFIG, $value);
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
    public function getEncodedProviders()
    {
        return $this->_get(self::ENCODED_PROVIDERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodedProviders($value)
    {
        $this->setData(self::ENCODED_PROVIDERS, $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultProvider()
    {
        return $this->_get(self::DEFAULT_PROVIDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultProvider($value)
    {
        $this->setData(self::DEFAULT_PROVIDER, $value);
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
        \MSP\TwoFactorAuth\Api\Data\UserConfigExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
