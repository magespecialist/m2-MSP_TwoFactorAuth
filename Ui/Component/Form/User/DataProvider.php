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
use MSP\TwoFactorAuth\Model\Trusted;

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

    /**
     * DataProvider constructor.
     * @param CollectionFactory $collectionFactory
     * @param EnabledProvider $enabledProvider
     * @param UserConfigManagerInterface $userConfigManager
     * @param UrlInterface $url
     * @param TfaInterface $tfa
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
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
            if ($provider->isConfigured($user->getId()) && $provider->isResetAllowed()) {
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

    /**
     * Get a list of trusted devices as array
     * @param User $user
     * @return array
     */
    private function getTrustedDevices(User $user)
    {
        $trustedDevices = $this->tfa->getTrustedDevices($user->getId());
        $res = [];

        foreach ($trustedDevices as $trustedDevice) {
            /** @var Trusted $trustedDevice */
            $revokeUrl = $this->url->getUrl('msp_twofactorauth/tfa/revoke', [
                'id' => $trustedDevice->getId(),
                'user_id' => $user->getId(),
            ]);

            $res[] = [
                'last_ip' => $trustedDevice->getLastIp(),
                'date_time' => $trustedDevice->getDateTime(),
                'device_name' => $trustedDevice->getDeviceName(),
                'revoke_url' => $revokeUrl,
            ];
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $meta['base_fieldset']['children']['msp_tfa_providers']['arguments']['data']['config']['forced_providers'] =
            $this->getForcedProviders();
        $meta['base_fieldset']['children']['msp_tfa_providers']['arguments']['data']['config']['enabled_providers'] =
            $this->enabledProvider->toOptionArray();

        return $meta;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        if ($this->loadedData === null) {
            $this->loadedData = [];
            $items = $this->collection->getItems();

            /** @var User $user */
            foreach ($items as $user) {
                $providerCodes = $this->userConfigManager->getProvidersCodes($user->getId());
                $resetProviders = $this->getResetProviderUrls($user);
                $trustedDevices = $this->getTrustedDevices($user);

                $data = [
                    'reset_providers' => $resetProviders,
                    'trusted_devices' => $trustedDevices,
                    'msp_tfa_providers' => $providerCodes,
                ];
                $this->loadedData[$user->getId()] = $data;
            }
        }

        return $this->loadedData;
    }
}
