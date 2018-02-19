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

namespace MSP\TwoFactorAuth\Plugin\Block\User\Edit;

use Magento\Framework\AuthorizationInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;

class TabsPlugin
{
    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * TabsPlugin constructor.
     * @param TfaInterface $tfa
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        TfaInterface $tfa,
        AuthorizationInterface $authorization
    ) {
        $this->tfa = $tfa;
        $this->authorization = $authorization;
    }

    /**
     * @param \Magento\User\Block\User\Edit\Tabs $subject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeToHtml(\Magento\User\Block\User\Edit\Tabs $subject)
    {
        if (empty($this->tfa->getAllEnabledProviders()) ||
            !$this->authorization->isAllowed('MSP_TwoFactorAuth::tfa')
        ) {
            return;
        }

        $tfaForm = $subject->getLayout()->renderElement('msp_twofactorauth_edit_user_form');

        $subject->addTabAfter(
            'msp_twofactorauth',
            [
                'label' => __('2FA'),
                'title' => __('2FA'),
                'content' => $tfaForm,
                'active' => true
            ],
            'roles_section'
        );
    }
}
