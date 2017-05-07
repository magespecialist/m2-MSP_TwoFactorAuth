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
    const XML_PATH_GENERAL_ENABLED = 'msp_securitysuite/twofactorauth/enabled';

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
}
