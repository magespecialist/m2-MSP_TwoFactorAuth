<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface UserConfigInterface extends ExtensibleDataInterface
{
    const ID = 'msp_tfa_user_config_id';
    const ENCODED_CONFIG = 'encoded_config';
    const USER_ID = 'user_id';
    const ENCODED_PROVIDERS = 'encoded_providers';
    const DEFAULT_PROVIDER = 'default_provider';

    /**
     * Get value for msp_tfa_user_config_id
     * @return int
     */
    public function getId();

    /**
     * Set value for msp_tfa_user_config_id
     * @param int $value
     * @return \MSP\TwoFactorAuth\Api\Data\UserConfigInterface
     */
    public function setId($value);

    /**
     * Get value for user_id
     * @return int
     */
    public function getUserId();

    /**
     * Set value for user_id
     * @param int $value
     * @return \MSP\TwoFactorAuth\Api\Data\UserConfigInterface
     */
    public function setUserId($value);

    /**
     * Get value for encoded_providers
     * @return string
     */
    public function getEncodedProviders();

    /**
     * Set value for encoded_providers
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\UserConfigInterface
     */
    public function setEncodedProviders($value);

    /**
     * Get value for default_provider
     * @return string
     */
    public function getDefaultProvider();

    /**
     * Set value for default_provider
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\UserConfigInterface
     */
    public function setDefaultProvider($value);

    /**
     * Retrieve existing extension attributes object or create a new one
     * @return \MSP\TwoFactorAuth\Api\Data\UserConfigInterfaceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     * @param \MSP\TwoFactorAuth\Api\Data\UserConfigInterfaceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \MSP\TwoFactorAuth\Api\Data\UserConfigInterfaceExtensionInterface $extensionAttributes
    );
}
