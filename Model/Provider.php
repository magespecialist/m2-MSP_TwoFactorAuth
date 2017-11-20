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

use MSP\TwoFactorAuth\Api\ProviderInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Api\EngineInterface;

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
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

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

    /**
     * @var string
     */
    private $icon;

    public function __construct(
        EngineInterface $engine,
        UserConfigManagerInterface $userConfigManager,
        $code,
        $name,
        $icon,
        $configureAction,
        $authAction,
        $extraActions = [],
        $canReset = true
    ) {
        $this->engine = $engine;
        $this->userConfigManager = $userConfigManager;
        $this->code = $code;
        $this->name = $name;
        $this->configureAction = $configureAction;
        $this->authAction = $authAction;
        $this->extraActions = $extraActions;
        $this->canReset = $canReset;
        $this->icon = $icon;
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getEngine()->isEnabled();
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
     * Get provider icon
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Return true if this provider configuration can be reset
     * @return boolean
     */
    public function isResetAllowed()
    {
        return $this->canReset;
    }

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function isTrustedDevicesAllowed()
    {
        return $this->engine->isTrustedDevicesAllowed();
    }

    /**
     * @inheritdoc
     */
    public function resetConfiguration($userId)
    {
        $this->userConfigManager->setProviderConfig($userId, $this->getCode(), null);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isConfigured($userId)
    {
        return $this->getConfiguration($userId) !== null;
    }

    /**
     * @inheritdoc
     */
    public function getConfiguration($userId)
    {
        return $this->userConfigManager->getProviderConfig($userId, $this->getCode());
    }

    /**
     * @inheritdoc
     */
    public function isActive($userId)
    {
        return $this->userConfigManager->isProviderConfigurationActive($userId, $this->getCode());
    }

    /**
     * @inheritdoc
     */
    public function activate($userId)
    {
        $this->userConfigManager->activateProviderConfiguration($userId, $this->getCode());
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
