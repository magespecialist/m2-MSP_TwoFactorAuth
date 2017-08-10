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

use Magento\Framework\Exception\LocalizedException;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Model\ProviderInterface;

interface TfaInterface
{
    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/general/enabled';
    const XML_PATH_FORCED_PROVIDERS = 'msp_securitysuite_twofactorauth/general/force_providers';

    /**
     * Return true if 2FA is enabled
     * @return boolean
     */
    public function getIsEnabled();

    /**
     * Get provider by code
     * @param string $providerCode
     * @param bool $onlyEnabled = true
     * @return ProviderInterface|null
     */
    public function getProvider($providerCode, $onlyEnabled = true);

    /**
     * Retrieve forced providers list
     * @return ProviderInterface[]
     */
    public function getForcedProviders();

    /**
     * Get a user provider
     * @param UserInterface $user
     * @return ProviderInterface[]
     */
    public function getUserProviders(UserInterface $user);

    /**
     * Get a list of providers
     * @return ProviderInterface[]
     */
    public function getAllProviders();

    /**
     * Get a list of providers
     * @return ProviderInterface[]
     */
    public function getAllEnabledProviders();

    /**
     * Return a list of trusted devices for given user id
     * @param int $userId
     * @return array
     */
    public function getTrustedDevices($userId);

    /**
     * Get allowed URLs
     * @return array
     */
    public function getAllowedUrls();

    /**
     * Returns a list of providers to configure/enroll
     * @param UserInterface $user
     * @return ProviderInterface[]
     */
    public function getProvidersToActivate(UserInterface $user);

    /**
     * Return true if a provider is allowed for a given user
     * @param UserInterface $user
     * @param string $providerCode
     * @return mixed
     */
    public function getProviderIsAllowed(UserInterface $user, $providerCode);
}
