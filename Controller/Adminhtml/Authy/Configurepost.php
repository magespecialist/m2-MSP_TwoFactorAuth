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
use MSP\SecuritySuiteCommon\Api\SecuritySuiteInterface;
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
     * @var Authy
     */
    private $authy;

    public function __construct(
        Action\Context $context,
        Session $session,
        Authy $authy,
        TfaInterface $tfa,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->tfa = $tfa;
        $this->authy = $authy;
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
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $request = $this->getRequest();

        try {
            $this->authy->requestPhoneNumberVerification(
                $this->getUser(),
                $request->getParam('tfa_country'),
                $request->getParam('tfa_phone'),
                $request->getParam('tfa_method')
            );

            $this->_eventManager->dispatch(SecuritySuiteInterface::EVENT, [
                'level' => 'info',
                'module' => 'MSP_TwoFactorAuth',
                'message' => 'New authy verification request via ' . $request->getParam('tfa_method'),
                'username' => $this->getUser()->getUserName(),
            ]);
        } catch (\Exception $e) {
            $this->_eventManager->dispatch(SecuritySuiteInterface::EVENT, [
                'level' => 'error',
                'module' => 'MSP_TwoFactorAuth',
                'message' => 'Authy verification request failure via ' . $request->getParam('tfa_method'),
                'username' => $this->getUser()->getUserName(),
                'additional' => $e->getMessage(),
            ]);

            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('*/*/configure');
        }

        return $this->_redirect('*/*/verify');
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
            $user &&
            $this->tfa->getProviderIsAllowed($user->getId(), Authy::CODE) &&
            !$this->tfa->getProvider(Authy::CODE)->isActive($user->getId());
    }
}
