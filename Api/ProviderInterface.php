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

interface ProviderInterface
{
    /**
     * Get provider name
     * @return string
     */
    public function getName();

    /**
     * Get provider code
     * @return string
     */
    public function getCode();

    /**
     * Return true if this provider is enabled
     * @return boolean
     */
    public function isEnabled();

    /**
     * Return a list of reserved actions accessible without 2FA
     * @return array
     */
    public function getAllowedExtraActions();

    /**
     * Return activation action
     * @return string
     */
    public function getActivatePath();

    /**
     * Return auth action
     * @return string
     */
    public function getAuthPath();

    /**
     * Return true if user has a full configuration
     * @param \Magento\User\Model\User $user
     * @return boolean
     */
    public function getUserIsConfigured(\Magento\User\Model\User $user);

    /**
     * Verify auth
     * @param \Magento\Framework\App\RequestInterface $request
     * @return boolean
     */
    public function verify(\Magento\Framework\App\RequestInterface $request);

    /**
     * Return true if allow trusted devices
     * @return boolean
     */
    public function allowTrustedDevices();
}
