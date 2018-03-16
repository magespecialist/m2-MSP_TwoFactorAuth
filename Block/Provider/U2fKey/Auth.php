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

namespace MSP\TwoFactorAuth\Block\Provider\U2fKey;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\Auth\Session;
use MSP\TwoFactorAuth\Model\Provider\Engine\U2fKey;

class Auth extends Template
{
    /**
     * @var U2fKey
     */
    private $u2fKey;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Template\Context $context,
        Session $session,
        U2fKey $u2fKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->u2fKey = $u2fKey;
        $this->session = $session;
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $this->jsLayout['components']['msp-twofactorauth-auth']['postUrl'] =
            $this->getUrl('*/*/authpost');

        $this->jsLayout['components']['msp-twofactorauth-auth']['successUrl'] =
            $this->getUrl($this->_urlBuilder->getStartupPageUrl());

        $this->jsLayout['components']['msp-twofactorauth-auth']['touchImageUrl'] =
            $this->getViewFileUrl('MSP_TwoFactorAuth::images/u2f/touch.png');

        $this->jsLayout['components']['msp-twofactorauth-auth']['authenticateData'] =
            $this->u2fKey->getAuthenticateData($this->session->getUser());

        return parent::getJsLayout();
    }
}
