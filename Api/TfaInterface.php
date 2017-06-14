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

namespace MSP\TwoFactorAuth\Api;

interface TfaInterface
{
    const TRUSTED_DEVICE_COOKIE = 'msp_tfa_trusted';
    const XML_PATH_ENABLED = 'msp_securitysuite/twofactorauth/enabled';
    const XML_PATH_ALLOW_TRUSTED_DEVICES = 'msp_securitysuite/twofactorauth/allow_trusted_devices';
    const XML_PATH_FORCE_ALL_USERS = 'msp_securitysuite/twofactorauth/force_all_users';

    /**
     * Return true if enabled
     * @return bool
     */
    public function getEnabled();

    /**
     * Return true if trusted devices are allowed
     * @return bool
     */
    public function getAllowTrustedDevices();

    /**
     * Return true if users are forced to use tfa
     * @return bool
     */
    public function getForceAllUsers();

    /**
     * Return a list of trusted devices for given user id
     * @param int $userId
     * @return array
     */
    public function getTrustedDevices($userId);

    /**
     * Return true if user must activate his TFA
     * @return bool
     */
    public function getUserMustActivateTfa();

    /**
     * Return true if user must authenticate via TFA
     * @return bool
     */
    public function getUserMustAuth();

    /**
     * Return true if user has TFA activated
     * @return bool
     */
    public function getUserTfaIsActive();

    /**
     * Render TFA QrCode
     */
    public function getQrCodeAsPng();

    /**
     * Get TFA provisioning URL
     * @return string
     */
    public function getProvisioningUrl();

    /**
     * Return true on token validation
     * @param $token
     * @return bool
     */
    public function verify($token);

    /**
     * Activate user TFA
     * @return TfaInterface
     * @throws \Exception
     */
    public function activateUserTfa();

    /**
     * Set TFA pass status
     * @param $status
     * @return TfaInterface
     */
    public function setTwoAuthFactorPassed($status);

    /**
     * Get TFA pass status
     * @return bool
     */
    public function getTwoAuthFactorPassed();

    /**
     * Trust device and return secret token
     * @return void
     */
    public function trustDevice();

    /**
     * Return true if device is trusted
     * @return bool
     */
    public function isTrustedDevice();

    /**
     * Rotate secret token
     * @return string
     */
    public function rotateToken();

    /**
     * Revoke trusted device
     * @param int $tokenId
     * @return void
     */
    public function revokeTrustedDevice($tokenId);
}
