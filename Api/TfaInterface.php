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
    const MAX_PROVIDERS = 2;
    const PROVIDER_DISABLE = 'disable';
    const PROVIDER_NO_GET_PARAM = 'msptfa_n';

    const TRUSTED_DEVICE_COOKIE = 'msp_tfa_trusted';
    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/general/enabled';
    const XML_PATH_FORCED_PROVIDER_PREFIX = 'msp_securitysuite_twofactorauth/general/force_provider_';

    /**
     * Get provider by code
     * @param string $providerCode
     * @return ProviderInterface|null|false
     */
    public function getProvider($providerCode);

    /**
     * Retrieve forced providers list
     * @return array
     */
    public function getForcedProvidersCodes();

    /**
     * Get a user provider
     * @param UserInterface $user
     * @param int $n
     * @return ProviderInterface|null
     */
    public function getUserProvider(UserInterface $user, $n);

    /**
     * Get a list of providers
     * @return ProviderInterface[]
     */
    public function getAllProviders();

    /**
     * Get forced provider:
     * Returns ProviderInterface if defined
     * Returns null if not defined
     * Returns false if admin denied the n-th provider
     * @param int $n
     * @return false|ProviderInterface|null
     * @throws LocalizedException
     */
    public function getForcedProvider($n);

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
