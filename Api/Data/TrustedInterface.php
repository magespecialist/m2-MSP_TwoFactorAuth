<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TrustedInterface extends ExtensibleDataInterface
{
    const ID = 'msp_tfa_trusted_id';
    const DATE_TIME = 'date_time';
    const USER_ID = 'user_id';
    const DEVICE_NAME = 'device_name';
    const TOKEN = 'token';
    const LAST_IP = 'last_ip';
    const USER_AGENT = 'user_agent';

    /**
     * Get value for msp_tfa_trusted_id
     * @return int
     */
    public function getId();

    /**
     * Set value for msp_tfa_trusted_id
     * @param int $value
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function setId($value);

    /**
     * Get value for date_time
     * @return string
     */
    public function getDateTime();

    /**
     * Set value for date_time
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function setDateTime($value);

    /**
     * Get value for user_id
     * @return int
     */
    public function getUserId();

    /**
     * Set value for user_id
     * @param int $value
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function setUserId($value);

    /**
     * Get value for device_name
     * @return string
     */
    public function getDeviceName();

    /**
     * Set value for device_name
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function setDeviceName($value);

    /**
     * Get value for last_ip
     * @return string
     */
    public function getLastIp();

    /**
     * Set value for last_ip
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function setLastIp($value);

    /**
     * Get value for user_agent
     * @return string
     */
    public function getUserAgent();

    /**
     * Set value for user_agent
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function setUserAgent($value);

    /**
     * Retrieve existing extension attributes object or create a new one
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     * @param \MSP\TwoFactorAuth\Api\Data\TrustedExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \MSP\TwoFactorAuth\Api\Data\TrustedExtensionInterface $extensionAttributes
    );
}
