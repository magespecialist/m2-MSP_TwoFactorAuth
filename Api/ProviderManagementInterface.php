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

interface ProviderManagementInterface
{
    const PROVIDER_NONE = 'none';
    const XML_PATH_FORCE_ALL_USERS = 'msp_securitysuite_twofactorauth/general/force_all_users';

    /**
     * Return a providers list
     * @return ProviderInterface[]
     */
    public function getAllProviders();

    /**
     * Return a provider by its code
     * @param string $code
     * @return ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProvider($code);

    /**
     * Return true if users are forced to use tfa
     * @return ProviderInterface|null
     */
    public function getForcedProvider();

    /**
     * Get user's provider
     * @param \Magento\User\Model\User $user = null
     * @return ProviderInterface|null
     */
    public function getUserProvider(\Magento\User\Model\User $user = null);

    /**
     * Save user TFA config
     * @param array $config
     * @param \Magento\User\Model\User $user = null
     * @param string $providerCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setUserProviderConfiguration($config, $providerCode, $user = null);

    /**
     * Get user TFA config
     * @param string $providerCode
     * @param \Magento\User\Model\User $user = null
     * @return array
     */
    public function getUserProviderConfiguration($providerCode, $user = null);

    /**
     * Reset user's configuration
     * @param $providerCode
     * @param \Magento\User\Model\User $user
     * @return boolean
     */
    public function reset($providerCode, \Magento\User\Model\User $user);
}
