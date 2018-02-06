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

namespace MSP\TwoFactorAuth\Controller\Adminhtml\Authy;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use MSP\SecuritySuiteCommon\Api\AlertInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Controller\Adminhtml\AbstractAction;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Configurepost extends AbstractAction
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var AlertInterface
     */
    private $alert;

    /**
     * @var Authy\Verification
     */
    private $verification;

    /**
     * Configurepost constructor.
     * @param Action\Context $context
     * @param Session $session
     * @param Authy\Verification $verification
     * @param TfaInterface $tfa
     * @param AlertInterface $alert
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Action\Context $context,
        Session $session,
        Authy\Verification $verification,
        TfaInterface $tfa,
        AlertInterface $alert,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->tfa = $tfa;
        $this->alert = $alert;
        $this->verification = $verification;
    }

    /**
     * Get current user
     * @return \Magento\User\Model\User|null
     */
    private function getUser()
    {
        return $this->session->getUser();
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $request = $this->getRequest();

        try {
            $this->verification->request(
                $this->getUser(),
                $request->getParam('tfa_country'),
                $request->getParam('tfa_phone'),
                $request->getParam('tfa_method')
            );

            $this->alert->event(
                'MSP_TwoFactorAuth',
                'New authy verification request via ' . $request->getParam('tfa_method'),
                AlertInterface::LEVEL_INFO,
                $this->getUser()->getUserName()
            );
        } catch (\Exception $e) {
            $this->alert->event(
                'MSP_TwoFactorAuth',
                'Authy verification request failure via ' . $request->getParam('tfa_method'),
                AlertInterface::LEVEL_ERROR,
                $this->getUser()->getUserName(),
                AlertInterface::ACTION_LOG,
                $e->getMessage()
            );

            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('*/*/configure');
        }

        return $this->_redirect('*/*/verify');
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        $user = $this->getUser();

        return
            $user &&
            $this->tfa->getProviderIsAllowed($user->getId(), Authy::CODE) &&
            !$this->tfa->getProvider(Authy::CODE)->isActive($user->getId());
    }
}
