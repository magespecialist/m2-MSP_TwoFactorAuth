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

namespace MSP\TwoFactorAuth\Controller\Adminhtml\Authpost;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\ManagerInterface;
use MSP\SecuritySuiteCommon\Api\LogManagementInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;

class Verify extends Action
{
    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ManagerInterface
     */
    private $event;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        Context $context,
        TfaInterface $tfa,
        Session $session
    ) {
        $this->tfa = $tfa;

        parent::__construct($context);
        $this->tfa = $tfa;
        $this->session = $session;
        $this->event = $context->getEventManager();
        $this->context = $context;
    }

    public function execute()
    {
        $tfaTrusted = $this->getRequest()->getParam('tfa_trusted');
        $provider = $this->tfa->getUserProvider($this->session->getUser());

        if ($provider->verify($this->getRequest())) {
            $this->tfa->setTwoAuthFactorPassed(true);
            if ($tfaTrusted && $provider->allowTrustedDevices()) {
                $this->tfa->trustDevice();
            }
        } else {
            $this->tfa->setTwoAuthFactorPassed(false);
            $this->event->dispatch(LogManagementInterface::EVENT_ACTIVITY, [
                'module' => 'MSP_TwoFactorAuth',
                'message' => 'Invalid token',
                'username' => $this->session->getUser()->getUserName(),
            ]);

            $this->messageManager->addErrorMessage('Invalid code');
        }

        return $this->_redirect('/');
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
