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

namespace MSP\TwoFactorAuth\Controller\Adminhtml\Google;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use MSP\SecuritySuiteCommon\Api\LogManagementInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TfaSessionInterface;
use MSP\TwoFactorAuth\Api\TrustedManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Google;
use Magento\Framework\Event\ManagerInterface as EventInterface;

class Authpost extends Action
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
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var Google
     */
    private $google;

    /**
     * @var TfaSessionInterface
     */
    private $tfaSession;

    /**
     * @var TrustedManagerInterface
     */
    private $trustedManager;

    /**
     * @var EventInterface
     */
    private $event;

    public function __construct(
        Action\Context $context,
        Session $session,
        PageFactory $pageFactory,
        Google $google,
        TfaSessionInterface $tfaSession,
        TrustedManagerInterface $trustedManager,
        EventInterface $event,
        TfaInterface $tfa
    ) {
        parent::__construct($context);
        $this->tfa = $tfa;
        $this->session = $session;
        $this->pageFactory = $pageFactory;
        $this->google = $google;
        $this->tfaSession = $tfaSession;
        $this->trustedManager = $trustedManager;
        $this->event = $event;
    }

    /**
     * Get current user
     * @return \Magento\User\Model\User|null
     */
    protected function getUser()
    {
        return $this->session->getUser();
    }

    public function execute()
    {
        $user = $this->getUser();

        if ($this->google->verify($user, $this->getRequest())) {
            $this->trustedManager->handleTrustDeviceRequest(Google::CODE, $this->getRequest());
            $this->tfaSession->grantAccess();
            return $this->_redirect('/');
        } else {
            $this->event->dispatch(LogManagementInterface::EVENT_ACTIVITY, [
                'module' => 'MSP_TwoFactorAuth',
                'message' => 'Access denied using Google Authenticator',
                'username' => $user->getUserName(),
            ]);

            $this->messageManager->addErrorMessage('Invalid code');
            return $this->_redirect('*/*/auth');
        }
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $user = $this->getUser();

        return
            $this->tfa->getProviderIsAllowed($this->getUser(), Google::CODE) &&
            $this->tfa->getProvider(Google::CODE)->getIsActive($user);
    }
}
