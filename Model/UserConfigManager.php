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

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;

class UserConfigManager implements UserConfigManagerInterface
{
    private $configurationRegistry = [];

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
     * @param int $userId
     * @param string $providerCode
     * @return array
     */
    public function getProviderConfig($userId, $providerCode)
    {
        $userConfig = $this->getUserConfiguration($userId);
        $providersConfig = $userConfig->getData('config');

        if (!isset($providersConfig[$providerCode])) {
            return null;
        }

        return $providersConfig[$providerCode];
    }

    /**
     * @inheritdoc
     */
    public function setProviderConfig($userId, $providerCode, $config)
    {
        $userConfig = $this->getUserConfiguration($userId);
        $providersConfig = $userConfig->getData('config');

        if ($config === null) {
            if (isset($providersConfig[$providerCode])) {
                unset($providersConfig[$providerCode]);
            }
        } else {
            $providersConfig[$providerCode] = $config;
        }

        $userConfig->setData('config', $providersConfig);
        $userConfig->getResource()->save($userConfig);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function addProviderConfig($userId, $providerCode, $config)
    {
        $userConfig = $this->getProviderConfig($userId, $providerCode);
        if ($userConfig === null) {
            $newConfig = $config;
        } else {
            $newConfig = array_merge($userConfig, $config);
        }

        return $this->setProviderConfig($userId, $providerCode, $newConfig);
    }

    /**
     * @inheritdoc
     */
    public function resetProviderConfig($userId, $providerCode)
    {
        $this->setProviderConfig($userId, $providerCode, null);
        return true;
    }

    /**
     * Get user TFA config
     * @param int $userId
     * @return UserConfig
     */
    private function getUserConfiguration($userId)
    {
        if (!isset($this->configurationRegistry[$userId])) {
            /** @var $userConfig UserConfig */
            $userConfig = $this->userConfigFactory->create();
            $userConfig->getResource()->load($userConfig, $userId, 'user_id');
            $userConfig->setData('user_id', $userId);

            $this->configurationRegistry[$userId] = $userConfig;
        }

        return $this->configurationRegistry[$userId];
    }

    /**
     * @inheritdoc
     */
    public function setProvidersCodes($userId, $providersCodes)
    {
        if (is_string($providersCodes)) {
            $providersCodes = preg_split('/\s*,\s*/', $providersCodes);
        }

        $userConfig = $this->getUserConfiguration($userId);
        $userConfig->setData('providers', $providersCodes);
        $userConfig->getResource()->save($userConfig);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getProvidersCodes($userId)
    {
        $userConfig = $this->getUserConfiguration($userId);
        return $userConfig->getData('providers');
    }

    /**
     * @inheritdoc
     */
    public function activateProviderConfiguration($userId, $providerCode)
    {
        return $this->addProviderConfig($userId, $providerCode, [
            UserConfigManagerInterface::ACTIVE_CONFIG_KEY => true
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isProviderConfigurationActive($userId, $providerCode)
    {
        $config = $this->getProviderConfig($userId, $providerCode);
        return $config &&
            isset($config[UserConfigManagerInterface::ACTIVE_CONFIG_KEY]) &&
            $config[UserConfigManagerInterface::ACTIVE_CONFIG_KEY];
    }

    /**
     * @inheritdoc
     */
    public function setDefaultProvider($userId, $providerCode)
    {
        $userConfig = $this->getUserConfiguration($userId);
        $userConfig->setData('default_provider', $providerCode);
        $userConfig->getResource()->save($userConfig);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultProvider($userId)
    {
        $userConfig = $this->getUserConfiguration($userId);
        return $userConfig->getData('default_provider');
    }
}
