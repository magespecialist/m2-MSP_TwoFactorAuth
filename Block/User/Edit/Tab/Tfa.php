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
use MSP\TwoFactorAuth\Api\Data\TrustedInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Model\Config\Source\Provider;

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
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->tfa = $tfa;
        $this->context = $context;
        $this->provider = $provider;
    }

    protected function _prepareForm()
    {
        /** @var $user \Magento\User\Model\User */
        $user = $this->_coreRegistry->registry('permissions_user');

        $regenerateUrl = $this->getUrl('msp_twofactorauth/regenerate/token', [
            'id' => $user->getId(),
        ]);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('tfa_');

        $tfaFieldset = $form->addFieldset(
            'base_fieldset', [
            'legend' => __('Two Factor Authentication')
        ]);

        $tfaFieldset->addField(
            'msp_tfa_provider',
            'select',
            [
                'value' => $user->getMspTfaProvider(),
                'name' => 'msp_tfa_provider',
                'label' => __('Two Factor Authentication'),
                'title' => __('Two Factor Authentication'),
                'options' => $this->provider->toArray(),
            ]
        );

        $tfaProvider = $this->tfa->getUserProvider($user);
        if (
            $tfaProvider &&
            $tfaProvider->canRegenerateToken($user)
        ) {
            $tfaFieldset->addField(
                'msp_tfa_regenerate',
                'label',
                [
                    'label' => __('Regenerate Auth'),
                    'name' => 'msp_tfa_regenerate',
                    'after_element_html' =>
                        '<button'
                        . ' type="button" '
                        . ' onclick="self.location.href=\'' . $regenerateUrl . '\'">'
                        . __('Regenerate Token')
                        . '</button>',
                ]
            );
        }

        if ($this->tfa->getAllowTrustedDevices()) {
            $trustedDevices = $this->tfa->getTrustedDevices($user->getId());

            // TODO: Make this better, my eyes are bleeding looking at this code
            if (count($trustedDevices)) {
                $devicesHtml = ['<div class="msp_tfa-trusted_devices">'];

                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-head">';
                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-last_ip">' . __('IP') . '</div>';
                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-date">' . __('Date Time') . '</div>';
                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-name">' . __('Device') . '</div>';
                $devicesHtml[] = '<div class="msp_tfa-trusted_devices-revoke">&nbsp;</div>';
                $devicesHtml[] = '</div>';

                foreach ($trustedDevices as $trustedDevice) {
                    /** @var $trustedDevice TrustedInterface */
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
            } else {
                $devicesHtml = [__('No trusted devices for this user')];
            }

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
