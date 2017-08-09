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
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TfaSessionInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Google;

class Configurepost extends Action
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

    public function __construct(
        Action\Context $context,
        Session $session,
        PageFactory $pageFactory,
        Google $google,
        TfaSessionInterface $tfaSession,
        TfaInterface $tfa
    ) {
        parent::__construct($context);
        $this->tfa = $tfa;
        $this->session = $session;
        $this->pageFactory = $pageFactory;
        $this->google = $google;
        $this->tfaSession = $tfaSession;
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
            $this->tfa->getProvider(Google::CODE)->activate($user);
            $this->tfaSession->grantAccess();

            return $this->_redirect('/');
        } else {
            $this->messageManager->addErrorMessage('Invalid code');
            return $this->_redirect('*/*/activate');
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
            !$this->tfa->getProvider(Google::CODE)->getIsActive($user);
    }
}
