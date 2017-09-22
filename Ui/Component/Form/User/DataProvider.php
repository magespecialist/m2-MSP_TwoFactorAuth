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

namespace MSP\TwoFactorAuth\Ui\Component\Form\User;

use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\User\Model\User;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Model\Config\Source\EnabledProvider;

class DataProvider extends AbstractDataProvider
{
    private $loadedData = null;

    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var EnabledProvider
     */
    private $enabledProvider;

    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        CollectionFactory $collectionFactory,
        EnabledProvider $enabledProvider,
        UserConfigManagerInterface $userConfigManager,
        UrlInterface $url,
        TfaInterface $tfa,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->tfa = $tfa;
        $this->enabledProvider = $enabledProvider;
        $this->userConfigManager = $userConfigManager;
        $this->url = $url;
    }

    /**
     * Get a list of forced providers
     * @return array
     */
    private function getForcedProviders()
    {
        $names = [];
        $forcedProviders = $this->tfa->getForcedProviders();
        if (!empty($forcedProviders)) {
            foreach ($forcedProviders as $forcedProvider) {
                $names[] = $forcedProvider->getName();
            }
        }

        return $names;
    }

    /**
     * Get reset provider urls
     * @param User $user
     * @return array
     */
    private function getResetProviderUrls(User $user)
    {
        $providers = $this->tfa->getAllEnabledProviders();

        $resetProviders = [];
        foreach ($providers as $provider) {
            if ($provider->isConfigured($user) && $provider->isResetAllowed()) {
                $resetProviders[] = [
                    'value' => $provider->getCode(),
                    'label' => __('Reset %1', $provider->getName()),
                    'url' => $this->url->getUrl('msp_twofactorauth/tfa/reset', [
                        'id' => $user->getId(),
                        'provider' => $provider->getCode(),
                    ]),
                ];
            }
        }

        return $resetProviders;
    }

    public function getData()
    {
        if ($this->loadedData === null) {
            $this->loadedData = [];
            $items = $this->collection->getItems();
            $forcedProviders = $this->getForcedProviders();
            $enabledProviders = $this->enabledProvider->toOptionArray();

            /** @var User $feed */
            foreach ($items as $user) {
                $providerCodes = $this->userConfigManager->getProvidersCodes($user);
                $resetProviders = $this->getResetProviderUrls($user);

                $data = [
                    'forced_providers' => $forcedProviders,
                    'enabled_providers' => $enabledProviders,
                    'reset_providers' => $resetProviders,
                    'msp_tfa_providers' => $providerCodes,
                ];
                $this->loadedData[$user->getId()] = $data;
            }
        }

        return $this->loadedData;
    }
}
