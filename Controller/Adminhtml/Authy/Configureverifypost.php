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
use Magento\Framework\Controller\Result\JsonFactory;
use MSP\SecuritySuiteCommon\Api\AlertInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TfaSessionInterface;
use MSP\TwoFactorAuth\Controller\Adminhtml\AbstractAction;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Configureverifypost extends AbstractAction
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

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

    /**
     * @var TfaSessionInterface
     */
    private $tfaSession;

    /**
     * @var AlertInterface
     */
    private $alert;

    /**
     * @var Authy\Verification
     */
    private $verification;

    /**
     * Verifypost constructor.
     * @param Action\Context $context
     * @param Session $session
     * @param TfaInterface $tfa
     * @param TfaSessionInterface $tfaSession
     * @param AlertInterface $alert
     * @param Authy $authy
     * @param Authy\Verification $verification
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Action\Context $context,
        Session $session,
        TfaInterface $tfa,
        TfaSessionInterface $tfaSession,
        AlertInterface $alert,
        Authy $authy,
        Authy\Verification $verification,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->session = $session;
        $this->tfa = $tfa;
        $this->tfaSession = $tfaSession;
        $this->alert = $alert;
        $this->verification = $verification;
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
     * @inheritdoc
     */
    public function execute()
    {
        $verificationCode = $this->getRequest()->getParam('tfa_verify');
        $response = $this->jsonFactory->create();

        try {
            $this->verification->verify($this->getUser(), $verificationCode);
            $this->authy->enroll($this->getUser());
            $this->tfaSession->grantAccess();

            $this->alert->event(
                'MSP_TwoFactorAuth',
                'Authy identity verified',
                AlertInterface::LEVEL_INFO,
                $this->getUser()->getUserName()
            );

            $response->setData([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            $this->alert->event(
                'MSP_TwoFactorAuth',
                'Authy identity verification failure',
                AlertInterface::LEVEL_ERROR,
                $this->getUser()->getUserName(),
                AlertInterface::ACTION_LOG,
                $e->getMessage()
            );

            $response->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return $response;
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
