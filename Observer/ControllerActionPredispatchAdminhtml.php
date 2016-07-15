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

namespace MSP\TwoFactorAuth\Observer;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;

class ControllerActionPredispatchAdminhtml implements ObserverInterface
{
    protected $tfaInterface;
    protected $actionFlag;
    protected $urlInterface;

    public function __construct(
        TfaInterface $tfaInterface,
        ActionFlag $actionFlag,
        UrlInterface $urlInterface
    ) {
        $this->tfaInterface = $tfaInterface;
        $this->actionFlag = $actionFlag;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var $controllerAction \Magento\Backend\App\AbstractAction */
        $controllerAction = $observer->getEvent()->getControllerAction();
        $fullActionName = $controllerAction->getRequest()->getFullActionName();

        if (in_array($fullActionName, [
            'adminhtml_auth_login',
            'adminhtml_auth_logout',

            'msp_twofactorauth_activate_index',
            'msp_twofactorauth_activate_qrcode',
            'msp_twofactorauth_activate_post',

            'msp_twofactorauth_auth_index',
            'msp_twofactorauth_auth_post',
            'msp_twofactorauth_auth_regenerate',
        ])) {
            return;
        }
        
        if ($this->tfaInterface->getUserMustActivateTfa()) {
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $url = $this->urlInterface->getUrl('msp_twofactorauth/activate/index');
            $controllerAction->getResponse()->setRedirect($url);

        } else if ($this->tfaInterface->getUserMustAuth()) {
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $url = $this->urlInterface->getUrl('msp_twofactorauth/auth/index');
            $controllerAction->getResponse()->setRedirect($url);
        }
    }
}
