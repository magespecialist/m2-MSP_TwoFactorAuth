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

namespace MSP\TwoFactorAuth\Controller\Adminhtml\Duo;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use MSP\SecuritySuiteCommon\Api\LogManagementInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TfaSessionInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\DuoSecurity;
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
     * @var TfaSessionInterface
     */
    private $tfaSession;

    /**
     * @var DuoSecurity
     */
    private $duoSecurity;

    /**
     * @var EventInterface
     */
    private $event;

    public function __construct(
        Action\Context $context,
        Session $session,
        PageFactory $pageFactory,
        DuoSecurity $duoSecurity,
        TfaSessionInterface $tfaSession,
        TfaInterface $tfa
    ) {
        parent::__construct($context);
        $this->tfa = $tfa;
        $this->session = $session;
        $this->pageFactory = $pageFactory;
        $this->tfaSession = $tfaSession;
        $this->duoSecurity = $duoSecurity;
        $this->event = $context->getEventManager();
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

        if ($this->duoSecurity->verify($user, $this->getRequest())) {
            $this->tfa->getProvider(DuoSecurity::CODE)->activate($this->getUser());
            $this->tfaSession->grantAccess();
            return $this->_redirect('/');
        } else {
            $this->event->dispatch(LogManagementInterface::EVENT_ACTIVITY, [
                'module' => 'MSP_TwoFactorAuth',
                'message' => 'DuoSecurity invalid auth',
                'username' => $user->getUserName(),
            ]);

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
        // Do not check for activation
        return
            $this->tfa->getProviderIsAllowed($this->getUser(), DuoSecurity::CODE);
    }
}
