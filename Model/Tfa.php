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
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Model\ResourceModel\Trusted as TrustedResourceModel;

class Tfa implements TfaInterface
{
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
     * @var array
     */
    private $allowedUrls;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TrustedResourceModel\CollectionFactory $collectionFactory,
        $providers = [],
        $allowedUrls = []
    ) {
        $this->providers = $providers;
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
        $this->allowedUrls = $allowedUrls;
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
     * @return ProviderInterface
     */
    public function getProvider($providerCode)
    {
        if (!$providerCode || !isset($this->providers[$providerCode])) {
            return null;
        }

        return $this->providers[$providerCode];
    }

    /**
     * Get forced provider
     * @param int $n
     * @return ProviderInterface|null|false
     */
    public function getForcedProvider($n)
    {
        $providerCode = $this->scopeConfig->getValue(TfaInterface::XML_PATH_FORCED_PROVIDER_PREFIX . $n);
        if ($providerCode == ProviderInterface::PROVIDER_DISABLE) {
            return false;
        }

        return $this->getProvider($providerCode);
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
        return array_merge($this->allowedUrls, [
            'adminhtml_auth_login',
            'adminhtml_auth_logout',
            'msp_twofactorauth_tfa_index'
        ]);
    }

    /**
     * Return true if auth is passed
     * @return boolean
     */
    public function getAuthPassed()
    {
        return false;
    }
}
