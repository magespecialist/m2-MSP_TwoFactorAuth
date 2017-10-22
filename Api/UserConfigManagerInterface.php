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

interface UserConfigManagerInterface
{
    const ACTIVE_CONFIG_KEY = 'active';

    /**
     * Get a provider configuration for a given user
     * @param int $userId
     * @param int $providerCode
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderConfig($userId, $providerCode);

    /**
     * Set provider configuration
     * @param int $userId
     * @param string $providerCode
     * @param array|null $config
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setProviderConfig($userId, $providerCode, $config);

    /**
     * Set provider configuration
     * @param int $userId
     * @param string $providerCode
     * @param array|null $config
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addProviderConfig($userId, $providerCode, $config);

    /**
     * Reset provider configuration
     * @param int $userId
     * @param string $providerCode
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resetProviderConfig($userId, $providerCode);

    /**
     * Set providers list for a given user
     * @param int $userId
     * @param string $providersCodes
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setProvidersCodes($userId, $providersCodes);

    /**
     * Set providers list for a given user
     * @param int $userId
     * @return string[]
     */
    public function getProvidersCodes($userId);

    /**
     * Activate a provider configuration
     * @param int $userId
     * @param string $providerCode
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function activateProviderConfiguration($userId, $providerCode);

    /**
     * Return true if a provider configuration has been activated
     * @param int $userId
     * @param string $providerCode
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isProviderConfigurationActive($userId, $providerCode);

    /**
     * Set default provider
     * @param int $userId
     * @param string $providerCode
     * @return boolean
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setDefaultProvider($userId, $providerCode);

    /**
     * get default provider
     * @param int $userId
     * @return string
     */
    public function getDefaultProvider($userId);
}
