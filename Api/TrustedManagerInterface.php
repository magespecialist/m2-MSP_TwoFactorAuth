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

interface TrustedManagerInterface
{
    const TRUSTED_DEVICE_COOKIE = 'msp_tfa_trusted';

    /**
     * Rotate secret trust token
     * @return boolean
     */
    public function rotateTrustedDeviceToken();

    /**
     * Return true if device is trusted
     * @return boolean
     */
    public function isTrustedDevice();

    /**
     * Revoke trusted device
     * @param int $tokenId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function revokeTrustedDevice($tokenId);

    /**
     * Trust a device
     * @param string $providerCode
     * @param \Magento\Framework\App\RequestInterface $request
     * @return boolean
     */
    public function handleTrustDeviceRequest($providerCode, \Magento\Framework\App\RequestInterface $request);
}
