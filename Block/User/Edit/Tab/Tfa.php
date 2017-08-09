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

namespace MSP\TwoFactorAuth\Block\User\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Model\Config\Source\Provider;
use MSP\TwoFactorAuth\Model\ProviderInterface;

class Tfa extends Generic
{
    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var Provider
     */
    private $provider;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TfaInterface $tfa,
        Provider $provider,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->tfa = $tfa;
        $this->provider = $provider;
    }

    protected function _prepareForm()
    {
        /** @var $user \Magento\User\Model\User */
        $user = $this->_coreRegistry->registry('permissions_user');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('tfa_');

        $tfaFieldset = $form->addFieldset(
            'base_fieldset', [
            'legend' => __('2FA')
        ]);

        $forcedProviders = $this->tfa->getForcedProviders();
        if (count($forcedProviders)) {
            $names = [];
            foreach ($forcedProviders as $forcedProvider) {
                $names[] = $forcedProvider->getName();
            }

            $tfaFieldset->addField(
                'msp_tfa_providers_names',
                'label',
                [
                    'name' => 'msp_tfa_providers_names',
                    'label' => __('2FA Providers'),
                    'title' => __('2FA Providers'),
                    'after_element_html' => implode(', ', $names),
                ]
            );
        } else {
            $tfaFieldset->addField(
                'msp_tfa_providers',
                'multiselect',
                [
                    'value' => $user->getData(),
                    'can_be_empty' => true,
                    'name' => 'msp_tfa_providers',
                    'label' => __('Providers'),
                    'title' => __('Providers'),
                    'values' => $this->provider->toOptionArray(),
                ]
            );
        }

        $providers = $this->tfa->getAllEnabledProviders();
        foreach ($providers as $provider) {
            /** @var ProviderInterface $provider */
            if ($provider->getIsConfigured($user) && $provider->getCanReset()) {
                $resetUrl = $this->getUrl('msp_twofactorauth/tfa/reset', [
                    'id' => $user->getId(),
                    'provider' => $provider->getCode(),
                ]);

                $tfaFieldset->addField(
                    'msp_tfa_reset_' . $provider->getCode(),
                    'label',
                    [
                        'label' => __('Reset %1', $provider->getName()),
                        'name' => 'msp_tfa_reset_' . $provider->getCode(),
                        'after_element_html' =>
                            '<button'
                            . ' type="button" '
                            . ' onclick="self.location.href=\'' . $resetUrl . '\'">'
                            . __('Reset')
                            . '</button>',
                    ]
                );
            }
        }

        $trustedDevices = $this->tfa->getTrustedDevices($user->getId());

        // TODO: Make it better
        if (count($trustedDevices)) {
            $devicesHtml = ['<div class="msp_tfa-trusted_devices">'];

            $devicesHtml[] = '<div class="msp_tfa-trusted_devices-head">';
            $devicesHtml[] = '<div class="msp_tfa-trusted_devices-last_ip">' . __('IP') . '</div>';
            $devicesHtml[] = '<div class="msp_tfa-trusted_devices-date">' . __('Date Time') . '</div>';
            $devicesHtml[] = '<div class="msp_tfa-trusted_devices-name">' . __('Device') . '</div>';
            $devicesHtml[] = '<div class="msp_tfa-trusted_devices-revoke">&nbsp;</div>';
            $devicesHtml[] = '</div>';

            foreach ($trustedDevices as $trustedDevice) {
                $revokeUrl = $this->getUrl('msp_twofactorauth/trusted/revoke', [
                    'id' => $trustedDevice->getId(),
                    'user_id' => $user->getId(),
                ]);

                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-row">';
                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-last_ip">'
                    . $trustedDevice->getLastIp() . '</div>';
                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-date">'
                    . $trustedDevice->getDateTime() . '</div>';
                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-name">'
                    . $trustedDevice->getDeviceName() . '</div>';
                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-revoke">'
                    . '<button type="button" onclick="self.location.href=\'' . $revokeUrl . '\'">'
                    . __('Revoke')
                    . '</button>'
                    . '</div>';
                $devicesHtml[] = '</div>';
            }
            $devicesHtml[] = '</div>';

            $tfaFieldset->addField(
                'msp_tfa_trusted',
                'label',
                [
                    'label' => __('Trusted Devices'),
                    'name' => 'msp_tfa_trusted',
                    'after_element_html' => implode("\n", $devicesHtml),
                ]
            );
        }

        $data = $user->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
