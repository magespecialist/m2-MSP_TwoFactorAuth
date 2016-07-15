<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Block\User\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;

class Tfa extends Generic
{
    protected function _prepareForm()
    {
        /** @var $user \Magento\User\Model\User */
        $user = $this->_coreRegistry->registry('permissions_user');

        $regenerateUrl = $this->getUrl('msp_twofactorauth/auth/regenerate', [
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
            'msp_tfa_enabled',
            'select',
            [
                'value' => $user->getMspTfaEnabled(),
                'name'  => 'msp_tfa_enabled',
                'label' => __('Enable'),
                'title' => __('Enable'),
                'options' => array(
                    0 => __('No'),
                    1 => __('Yes'),
                ),
            ]
        );

        if ($user->getMspTfaEnabled() && $user->getMspTfaActivated()) {
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

        $data = $user->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
