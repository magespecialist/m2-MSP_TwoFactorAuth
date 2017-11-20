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
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled();

    /**
     * Get provider engine
     * @return \MSP\TwoFactorAuth\Api\EngineInterface
     */
    public function getEngine();

    /**
     * Get provider code
     * @return string
     */
    public function getCode();

    /**
     * Get provider name
     * @return string
     */
    public function getName();

    /**
     * Get icon
     * @return string
     */
    public function getIcon();

    /**
     * Return true if this provider configuration can be reset
     * @return boolean
     */
    public function isResetAllowed();

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function isTrustedDevicesAllowed();

    /**
     * Reset provider configuration
     * @param int $userId
     * @return \MSP\TwoFactorAuth\Api\ProviderInterface
     */
    public function resetConfiguration($userId);

    /**
     * Return true if this provider has been configured
     * @param int $userId
     * @return bool
     */
    public function isConfigured($userId);

    /**
     * Return true if current provider has been activated
     * @param int $userId
     * @return bool
     */
    public function isActive($userId);

    /**
     * Activate provider
     * @param int $userId
     * @return \MSP\TwoFactorAuth\Api\ProviderInterface
     */
    public function activate($userId);

    /**
     * Get configure action
     * @return string
     */
    public function getConfigureAction();

    /**
     * Get auth action
     * @return string
     */
    public function getAuthAction();

    /**
     * Get allowed extra actions
     * @return string[]
     */
    public function getExtraActions();
}
