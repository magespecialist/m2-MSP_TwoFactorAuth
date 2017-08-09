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
 * @package    MSP_NoSpam
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Model;

use Magento\User\Api\Data\UserInterface;
use Magento\User\Block\User;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagementInterface;
use MSP\TwoFactorAuth\Model\Provider\EngineInterface;

class Provider implements ProviderInterface
{
    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $allowTrustedDevices;

    /**
     * @var UserConfigManagementInterface
     */
    private $userConfigManagement;

    /**
     * @var string
     */
    private $configureAction;

    /**
     * @var string
     */
    private $authAction;

    /**
     * @var string[]
     */
    private $extraActions;
    /**
     * @var bool
     */
    private $canReset;


    public function __construct(
        EngineInterface $engine,
        UserConfigManagementInterface $userConfigManagement,
        $code,
        $name,
        $configureAction,
        $authAction,
        $extraActions = [],
        $canReset = true,
        $allowTrustedDevices = true
    ) {
        $this->engine = $engine;
        $this->userConfigManagement = $userConfigManagement;
        $this->code = $code;
        $this->name = $name;
        $this->allowTrustedDevices = $allowTrustedDevices;
        $this->configureAction = $configureAction;
        $this->authAction = $authAction;
        $this->extraActions = $extraActions;
        $this->canReset = $canReset;
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function getIsEnabled()
    {
        return $this->getEngine()->getIsEnabled();
    }

    /**
     * Get provider engine
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Get provider code
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get provider name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return true if this provider configuration can be reset
     * @return boolean
     */
    public function getCanReset()
    {
        return $this->canReset;
    }

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function getAllowTrustedDevices()
    {
        return $this->allowTrustedDevices;
    }

    /**
     * Reset provider configuration
     * @param UserInterface $user
     * @return $this
     */
    public function resetConfiguration(UserInterface $user)
    {
        $this->userConfigManagement->setProviderConfig($user, $this->getCode(), null);
        return $this;
    }

    /**
     * Return true if this provider has been configured
     * @param UserInterface $user
     * @return bool
     */
    public function getIsConfigured(UserInterface $user)
    {
        return !is_null($this->getConfiguration($user));
    }

    /**
     * Get user configuration
     * @param UserInterface $user
     * @return array|null
     */
    public function getConfiguration(UserInterface $user)
    {
        return $this->userConfigManagement->getProviderConfig($user, $this->getCode());
    }

    /**
     * Return true if current provider has been activated
     * @param UserInterface $user
     * @return bool
     */
    public function getIsActive(UserInterface $user)
    {
        return $this->userConfigManagement->getProviderConfigurationIsActive($user, $this->getCode());
    }

    /**
     * Activate provider
     * @param UserInterface $user
     * @return $this
     */
    public function activate(UserInterface $user)
    {
        $this->userConfigManagement->activateProviderConfiguration($user, $this->getCode());
        return $this;
    }

    /**
     * Get configure action
     * @return string
     */
    public function getConfigureAction()
    {
        return $this->configureAction;
    }

    /**
     * Get auth action
     * @return string
     */
    public function getAuthAction()
    {
        return $this->authAction;
    }

    /**
     * Get allowed extra actions
     * @return string[]
     */
    public function getExtraActions()
    {
        return $this->extraActions;
    }
}
