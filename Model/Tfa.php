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
        return $this->providers;
    }

    /**
     * Get provider by code
     * @param string $providerCode
     * @return ProviderInterface|null|false
     */
    public function getProvider($providerCode)
    {
        if ($providerCode == TfaInterface::PROVIDER_DISABLE) {
            return false;
        }

        if (!$providerCode || !isset($this->providers[$providerCode])) {
            return null;
        }

        return $this->providers[$providerCode];
    }

    /**
     * Retrieve forced providers list
     * @return array
     */
    public function getForcedProvidersCodes()
    {
        if (is_null($this->forcedProviders)) {
            $this->forcedProviders = [];

            for ($i = 0; $i < TfaInterface::MAX_PROVIDERS; $i++) {
                $this->forcedProviders[] =
                    $this->scopeConfig->getValue(TfaInterface::XML_PATH_FORCED_PROVIDER_PREFIX . $i);
            }
        }

        return $this->forcedProviders;
    }

    /**
     * Get a user provider
     * @param UserInterface $user
     * @param int $n
     * @return ProviderInterface|null
     */
    public function getUserProvider(UserInterface $user, $n)
    {
        // Check if there is any forced provider
        $forcedProvider = $this->getForcedProvider($n);
        if ($forcedProvider) {
            return $forcedProvider; // A forced provider is defined
        }

        $providersCodes = $this->userConfigManagement->getProvidersCodes($user);

        if (($forcedProvider !== false) && isset($providersCodes[$n])) {
            $userProvider = $this->getProvider($providersCodes[$n]);
            if ($userProvider) {
                return $userProvider;
            }
        }

        if ($n <= 0) {
            return null;
        }

        return $this->getUserProvider($user, $n-1);
    }

    /**
     * Get forced provider:
     * Returns ProviderInterface if defined
     * Returns null if not defined
     * Returns false if admin denied the n-th provider
     * @param int $n
     * @return false|ProviderInterface|null
     * @throws LocalizedException
     */
    public function getForcedProvider($n)
    {
        if ($n >= TfaInterface::MAX_PROVIDERS) {
            throw new LocalizedException(__('Provider %1 does not exist', $n));
        }

        $providersCodes = $this->getForcedProvidersCodes();
        return $this->getProvider($providersCodes[$n]);
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
        $providersCodes = array_merge(
            $this->getForcedProvidersCodes(),
            $this->userConfigManagement->getProvidersCodes($user)
        );

        $res = [];
        foreach ($providersCodes as $providerCode) {
            if ($provider = $this->getProvider($providerCode)) {
                if (!$provider->getIsActive($user)) {
                    $res[] = $provider;
                }
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
        for ($i=0; $i<TfaInterface::MAX_PROVIDERS; $i++) {
            if ($this->getUserProvider($user, $i)->getCode() == $providerCode) {
                return true;
            }
        }

        return false;
    }
}
