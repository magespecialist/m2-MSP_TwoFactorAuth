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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagementInterface;
use MSP\TwoFactorAuth\Model\ResourceModel\Trusted as TrustedResourceModel;

class Tfa implements TfaInterface
{
    protected $forcedProviders = null;
    protected $allowedUrls = null;

    /**
     * @var ProviderInterface[]
     */
    private $providers;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TrustedResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var UserConfigManagementInterface
     */
    private $userConfigManagement;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TrustedResourceModel\CollectionFactory $collectionFactory,
        UserConfigManagementInterface $userConfigManagement,
        $providers = []
    ) {
        $this->providers = $providers;
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->userConfigManagement = $userConfigManagement;
    }

    /**
     * Get a list of providers
     * @return ProviderInterface[]
     */
    public function getAllProviders()
    {
        return array_values($this->providers);
    }

    /**
     * Get a list of providers
     * @return ProviderInterface[]
     */
    public function getAllEnabledProviders()
    {
        if (!$this->getIsEnabled()) {
            return [];
        }

        $res = [];

        $providers = $this->getAllProviders();
        foreach ($providers as $provider) {
            if ($provider->getIsEnabled()) {
                $res[] = $provider;
            }
        }

        return $res;
    }

    /**
     * Get provider by code
     * @param string $providerCode
     * @param bool $onlyEnabled = true
     * @return ProviderInterface|null
     */
    public function getProvider($providerCode, $onlyEnabled = true)
    {
        if (!$providerCode || !isset($this->providers[$providerCode])) {
            return null;
        }

        if ($onlyEnabled && !$this->providers[$providerCode]->getIsEnabled()) {
            return null;
        }

        return $this->providers[$providerCode];
    }

    /**
     * Retrieve forced providers list
     * @return ProviderInterface[]
     */
    public function getForcedProviders()
    {
        if (is_null($this->forcedProviders)) {
            $forcedProvidersCodes =
                preg_split('/\s*,\s*/', $this->scopeConfig->getValue(TfaInterface::XML_PATH_FORCED_PROVIDERS));

            $this->forcedProviders = [];

            foreach ($forcedProvidersCodes as $forcedProviderCode) {
                $provider = $this->getProvider($forcedProviderCode);
                if ($provider) {
                    $this->forcedProviders[] = $provider;
                }
            }
        }

        return $this->forcedProviders;
    }

    /**
     * Get a user provider
     * @param UserInterface $user
     * @return ProviderInterface[]
     */
    public function getUserProviders(UserInterface $user)
    {
        $forcedProviders = $this->getForcedProviders();

        if (count($forcedProviders)) {
            return $forcedProviders;
        }

        $providersCodes = $this->userConfigManagement->getProvidersCodes($user);

        $res = [];
        foreach ($providersCodes as $providerCode) {
            $provider = $this->getProvider($providerCode);
            if ($provider) {
                $res[] = $provider;
            }
        }

        return $res;
    }

    /**
     * Return a list of trusted devices for given user id
     * @param int $userId
     * @return array
     */
    public function getTrustedDevices($userId)
    {
        /** @var $collection TrustedResourceModel\Collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('user_id', $userId);

        return $collection->getItems();
    }

    /**
     * Get allowed URLs
     * @return array
     */
    public function getAllowedUrls()
    {
        if (is_null($this->allowedUrls)) {
            $this->allowedUrls = [
                'adminhtml_auth_login',
                'adminhtml_auth_logout',
                'msp_twofactorauth_tfa_index'
            ];

            $providers = $this->getAllProviders();
            foreach ($providers as $provider) {
                $this->allowedUrls[] = str_replace('/', '_', $provider->getConfigureAction());
                $this->allowedUrls[] = str_replace('/', '_', $provider->getAuthAction());

                foreach (array_values($provider->getExtraActions()) as $extraAction) {
                    $this->allowedUrls[] = str_replace('/', '_', $extraAction);
                }
            }
        }

        return $this->allowedUrls;
    }

    /**
     * Returns a list of providers to activate/enroll
     * @param UserInterface $user
     * @return ProviderInterface[]
     */
    public function getProvidersToActivate(UserInterface $user)
    {
        $providers = $this->getUserProviders($user);

        $res = [];
        foreach ($providers as $provider) {
            if (!$provider->getIsActive($user)) {
                $res[] = $provider;
            }
        }

        return $res;
    }

    /**
     * Return true if a provider is allowed for a given user
     * @param UserInterface $user
     * @param string $providerCode
     * @return mixed
     */
    public function getProviderIsAllowed(UserInterface $user, $providerCode)
    {
        $providers = $this->getUserProviders($user);
        foreach ($providers as $provider) {
            if ($provider->getCode() == $providerCode) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true if 2FA is enabled
     * @return boolean
     */
    public function getIsEnabled()
    {
        return !!$this->scopeConfig->getValue(TfaInterface::XML_PATH_ENABLED);
    }
}
