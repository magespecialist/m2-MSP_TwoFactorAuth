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

use Magento\User\Api\Data\UserInterface;

interface UserConfigManagerInterface
{
    const ACTIVE_CONFIG_KEY = 'active';

    /**
     * Get a provider configuration for a given user
     * @param UserInterface $user
     * @param string $providerCode
     * @return array
     */
    public function getProviderConfig(UserInterface $user, $providerCode);

    /**
     * Set provider configuration
     * @param UserInterface $user
     * @param string $providerCode
     * @param array|null $config
     * @return $this
     */
    public function setProviderConfig(UserInterface $user, $providerCode, $config);

    /**
     * Reset provider configuration
     * @param UserInterface $user
     * @param $providerCode
     * @return $this
     */
    public function resetProviderConfig(UserInterface $user, $providerCode);

    /**
     * Set providers list for a given user
     * @param UserInterface $user
     * @param array $providers
     * @return $this
     */
    public function setProvidersCodes(UserInterface $user, array $providers);

    /**
     * Set providers list for a given user
     * @param UserInterface $user
     * @return array
     */
    public function getProvidersCodes(UserInterface $user);

    /**
     * Activate a provider configuration
     * @param UserInterface $user
     * @param $providerCode
     * @return $this
     */
    public function activateProviderConfiguration(UserInterface $user, $providerCode);

    /**
     * Return true if a provider configuration has been activated
     * @param UserInterface $user
     * @param $providerCode
     * @return boolean
     */
    public function getProviderConfigurationIsActive(UserInterface $user, $providerCode);

    /**
     * Set default provider
     * @param UserInterface $user
     * @param string $providerCode
     * @return $this
     */
    public function setDefaultProvider(UserInterface $user, $providerCode);

    /**
     * get default provider
     * @param UserInterface $user
     * @return string
     */
    public function getDefaultProvider(UserInterface $user);
}
