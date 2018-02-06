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

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use MSP\TwoFactorAuth\Api\ProviderPoolInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TrustedRepositoryInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Tfa implements TfaInterface
{
    private $forcedProviders = null;
    private $allowedUrls = null;
    private $enabledProviders = null;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var TrustedRepositoryInterface
     */
    private $trustedRepository;

    /**
     * @var ProviderPoolInterface
     */
    private $providerPool;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TrustedRepositoryInterface $trustedRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UserConfigManagerInterface $userConfigManager,
        ProviderPoolInterface $providerPool
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->userConfigManager = $userConfigManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->trustedRepository = $trustedRepository;
        $this->providerPool = $providerPool;
    }

    /**
     * @inheritdoc
     */
    public function getAllProviders()
    {
        return array_values($this->providerPool->getProviders());
    }

    /**
     * @inheritdoc
     */
    public function getProviderByCode($code)
    {
        if ($code) {
            try {
                return $this->providerPool->getProviderByCode($code);
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getAllEnabledProviders()
    {
        if ($this->enabledProviders === null) {
            $this->enabledProviders = [];

            if ($this->isEnabled()) {
                $providers = $this->getAllProviders();
                foreach ($providers as $provider) {
                    if ($provider->isEnabled()) {
                        $this->enabledProviders[] = $provider;
                    }
                }
            }
        }

        return $this->enabledProviders;
    }

    /**
     * @inheritdoc
     */
    public function getProvider($providerCode, $onlyEnabled = true)
    {
        $provider = $this->getProviderByCode($providerCode);

        if (!$provider) {
            return null;
        }

        if ($onlyEnabled && !$provider->isEnabled()) {
            return null;
        }

        return $provider;
    }

    /**
     * @inheritdoc
     */
    public function getForcedProviders()
    {
        if ($this->forcedProviders === null) {
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
     * @inheritdoc
     */
    public function getUserProviders($userId)
    {
        $forcedProviders = $this->getForcedProviders();

        if (!empty($forcedProviders)) {
            return $forcedProviders;
        }

        $providersCodes = $this->userConfigManager->getProvidersCodes($userId);

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
     * @inheritdoc
     */
    public function getTrustedDevices($userId)
    {
        $criteria = $this->searchCriteriaBuilder->addFilter('user_id', $userId)->create();
        $results = $this->trustedRepository->getList($criteria);

        return $results->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getAllowedUrls()
    {
        if ($this->allowedUrls === null) {
            $this->allowedUrls = [
                'adminhtml_auth_login',
                'adminhtml_auth_logout',
                'adminhtml_auth_forgotpassword',
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
     * @inheritdoc
     */
    public function getProvidersToActivate($userId)
    {
        $providers = $this->getUserProviders($userId);

        $res = [];
        foreach ($providers as $provider) {
            if (!$provider->isActive($userId)) {
                $res[] = $provider;
            }
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function getProviderIsAllowed($userId, $providerCode)
    {
        $providers = $this->getUserProviders($userId);
        foreach ($providers as $provider) {
            if ($provider->getCode() == $providerCode) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return !!$this->scopeConfig->getValue(TfaInterface::XML_PATH_ENABLED);
    }

    /**
     * Return true if a provider code is allowed
     * @param int $userId
     * @param string $providerCode
     * @return bool
     * @throws NoSuchEntityException
     */
    private function checkAllowedProvider($userId, $providerCode)
    {
        if (!$this->getProviderIsAllowed($userId, $providerCode)) {
            throw new NoSuchEntityException(__('Unknown or not enabled provider %1 for this user', $providerCode));
        }

        return true;
    }

    /**
     * Get default provider code
     * @param int $userId
     * @return string
     */
    public function getDefaultProviderCode($userId)
    {
        return $this->userConfigManager->getDefaultProvider($userId);
    }

    /**
     * Set default provider code
     * @param int $userId
     * @param string $providerCode
     * @return boolean
     * @throws NoSuchEntityException
     */
    public function setDefaultProviderCode($userId, $providerCode)
    {
        $this->checkAllowedProvider($userId, $providerCode);
        return $this->userConfigManager->setDefaultProvider($userId, $providerCode);
    }

    /**
     * Reset default provider code
     * @param int $userId
     * @param string $providerCode
     * @return boolean
     * @throws NoSuchEntityException
     */
    public function resetProviderConfig($userId, $providerCode)
    {
        $this->checkAllowedProvider($userId, $providerCode);
        return $this->userConfigManager->resetProviderConfig($userId, $providerCode);
    }

    /**
     * Set providers
     * @param int $userId
     * @param string $providersCodes
     * @return boolean
     * @throws NoSuchEntityException
     */
    public function setProvidersCodes($userId, $providersCodes)
    {
        if (is_string($providersCodes)) {
            $providersCodes = preg_split('/\s*,\s*/', $providersCodes);
        }

        foreach ($providersCodes as $providerCode) {
            $this->checkAllowedProvider($userId, $providerCode);
        }

        return $this->userConfigManager->setProvidersCodes($userId, $providersCodes);
    }
}
