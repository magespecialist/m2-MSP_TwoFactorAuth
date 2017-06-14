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

namespace MSP\TwoFactorAuth\Api\Data;

interface TrustedInterface
{
    const ID = 'msp_tfa_trusted_id';
    const DATE_TIME = 'date_time';
    const USER_ID = 'user_id';
    const DEVICE_NAME = 'device_name';
    const USER_AGENT = 'user_agent';
    const TOKEN = 'token';
    const LAST_IP = 'last_ip';

    /**
     * Get value for msp_tfa_trusted_id
     * @return int
     */
    public function getId();

    /**
     * Get value for date_time
     * @return string
     */
    public function getDateTime();

    /**
     * Get value for user_id
     * @return int
     */
    public function getUserId();

    /**
     * Get value for device_name
     * @return string
     */
    public function getDeviceName();

    /**
     * Get value for user_agent
     * @return string
     */
    public function getUserAgent();

    /**
     * Get value for token
     * @return string
     */
    public function getToken();

    /**
     * Get value for last_ip
     * @return string
     */
    public function getLastIp();

    /**
     * Set value for msp_tfa_trusted_id
     * @param int $value
     * @return $this
     */
    public function setId($value);

    /**
     * Set value for date_time
     * @param string $value
     * @return $this
     */
    public function setDateTime($value);

    /**
     * Set value for user_id
     * @param int $value
     * @return $this
     */
    public function setUserId($value);

    /**
     * Set value for device_name
     * @param string $value
     * @return $this
     */
    public function setDeviceName($value);

    /**
     * Set value for user_agent
     * @param string $value
     * @return $this
     */
    public function setUserAgent($value);

    /**
     * Set value for token
     * @param string $value
     * @return $this
     */
    public function setToken($value);

    /**
     * Set value for last_ip
     * @param string $value
     * @return $this
     */
    public function setLastIp($value);
}
