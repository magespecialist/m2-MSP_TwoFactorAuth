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

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;

class UserConfigManager implements UserConfigManagerInterface
{
    protected $configurationRegistry = [];

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var UserConfigFactory
     */
    private $userConfigFactory;

    public function __construct(
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        UserConfigFactory $userConfigFactory
    ) {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->userConfigFactory = $userConfigFactory;
    }

    /**
     * Get a provider configuration for a given user
     * @param UserInterface $user
     * @param string $providerCode
     * @return array
     */
    public function getProviderConfig(UserInterface $user, $providerCode)
    {
        $userConfig = $this->getUserConfiguration($user);
        $providersConfig = $userConfig->getData('config');

        if (!isset($providersConfig[$providerCode])) {
            return null;
        }

        return $providersConfig[$providerCode];
    }

    /**
     * Set provider configuration
     * @param UserInterface $user
     * @param string $providerCode
     * @param array|null $config
     * @return $this
     */
    public function setProviderConfig(UserInterface $user, $providerCode, $config)
    {
        $userConfig = $this->getUserConfiguration($user);
        $providersConfig = $userConfig->getData('config');

        if (is_null($config)) {
            if (isset($providersConfig[$providerCode])) {
                unset($providersConfig[$providerCode]);
            }
        } else {
            $providersConfig[$providerCode] = $config;
        }

        $userConfig->setData('config', $providersConfig);
        $userConfig->getResource()->save($userConfig);
        return $this;
    }

    /**
     * Reset provider configuration
     * @param UserInterface $user
     * @param $providerCode
     * @return $this
     */
    public function resetProviderConfig(UserInterface $user, $providerCode)
    {
        $this->setProviderConfig($user, $providerCode, null);
        return $this;
    }

    /**
     * Get user TFA config
     * @param UserInterface $user
     * @return UserConfig
     */
    protected function getUserConfiguration(UserInterface $user)
    {
        $key = $user->getId();

        if (!isset($this->configurationRegistry[$key])) {
            /** @var $userConfig UserConfig */
            $userConfig = $this->userConfigFactory->create();
            $userConfig->getResource()->load($userConfig, $user->getId(), 'user_id');
            $userConfig->setData('user_id', $user->getId());

            $this->configurationRegistry[$key] = $userConfig;
        }

        return $this->configurationRegistry[$key];
    }

    /**
     * Set providers list for a given user
     * @param UserInterface $user
     * @param array $providers
     * @return $this
     */
    public function setProvidersCodes(UserInterface $user, array $providers)
    {
        $userConfig = $this->getUserConfiguration($user);
        $userConfig->setData('providers', $providers);
        $userConfig->getResource()->save($userConfig);

        return $this;
    }

    /**
     * Set providers list for a given user
     * @param UserInterface $user
     * @return array
     */
    public function getProvidersCodes(UserInterface $user)
    {
        $userConfig = $this->getUserConfiguration($user);
        return $userConfig->getData('providers');
    }

    /**
     * Activate a provider configuration
     * @param UserInterface $user
     * @param $providerCode
     * @return $this
     */
    public function activateProviderConfiguration(UserInterface $user, $providerCode)
    {
        $config = $this->getProviderConfig($user, $providerCode);
        if (!$config) {
            $config = [];
        }

        $config[UserConfigManagerInterface::ACTIVE_CONFIG_KEY] = true;
        $this->setProviderConfig($user, $providerCode, $config);
        return $this;
    }

    /**
     * Return true if a provider configuration has been activated
     * @param UserInterface $user
     * @param $providerCode
     * @return boolean
     */
    public function getProviderConfigurationIsActive(UserInterface $user, $providerCode)
    {
        $config = $this->getProviderConfig($user, $providerCode);
        return $config &&
            isset($config[UserConfigManagerInterface::ACTIVE_CONFIG_KEY]) &&
            $config[UserConfigManagerInterface::ACTIVE_CONFIG_KEY];
    }
}
