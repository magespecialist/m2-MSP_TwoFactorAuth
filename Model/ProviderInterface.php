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

namespace MSP\TwoFactorAuth\Model;

use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Model\Provider\EngineInterface;

interface ProviderInterface
{
    /**
     * Get provider engine
     * @return EngineInterface
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
     * Return true if this provider can be used as secondary method
     * @return boolean
     */
    public function getCanBeSecondary();

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function getAllowTrustedDevices();

    /**
     * Return true if this provider allows resetting configuration
     * @return boolean
     */
    public function getRequiresConfiguration();

    /**
     * Reset provider configuration
     * @param UserInterface $user
     * @return $this
     */
    public function resetConfiguration(UserInterface $user);

    /**
     * Return true if this provider has been configured
     * @param UserInterface $user
     * @return bool
     */
    public function getIsConfigured(UserInterface $user);

    /**
     * Return true if current provider has been activated
     * @param UserInterface $user
     * @return bool
     */
    public function getIsActive(UserInterface $user);

    /**
     * Activate provider
     * @param UserInterface $user
     * @return $this
     */
    public function activate(UserInterface $user);

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
