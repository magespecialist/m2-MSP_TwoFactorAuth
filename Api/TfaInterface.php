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

use MSP\TwoFactorAuth\Model\ProviderInterface;

interface TfaInterface
{
    const TRUSTED_DEVICE_COOKIE = 'msp_tfa_trusted';
    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/general/enabled';
    const XML_PATH_FORCED_PROVIDER_PREFIX = 'msp_securitysuite_twofactorauth/general/force_provider_';
    const MAX_PROVIDERS = 2;

    /**
     * Get provider by code
     * @param string $providerCode
     * @return ProviderInterface|null
     */
    public function getProvider($providerCode);

    /**
     * Get a list of providers
     * @return ProviderInterface[]
     */
    public function getAllProviders();

    /**
     * Get forced provider
     * @param int $n
     * @return ProviderInterface|null|false
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
     * Return true if auth is passed
     * @return boolean
     */
    public function getAuthPassed();
}
