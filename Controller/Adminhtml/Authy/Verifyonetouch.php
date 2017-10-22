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

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TfaSessionInterface;
use MSP\TwoFactorAuth\Api\TrustedManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy;
use Magento\Framework\Event\ManagerInterface as EventInterface;

class Verifyonetouch extends Action
{
    /**
     * @var Authy
     */
    private $authy;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var TrustedManagerInterface
     */
    private $trustedManager;

    /**
     * @var TfaSessionInterface
     */
    private $tfaSession;

    /**
     * @var EventInterface
     */
    private $event;

    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        TrustedManagerInterface $trustedManager,
        TfaSessionInterface $tfaSession,
        TfaInterface $tfa,
        Authy $authy,
        Session $session
    ) {
        parent::__construct($context);
        $this->authy = $authy;
        $this->session = $session;
        $this->jsonFactory = $jsonFactory;
        $this->tfa = $tfa;
        $this->trustedManager = $trustedManager;
        $this->tfaSession = $tfaSession;
        $this->event = $context->getEventManager();
    }

    /**
     * Get current user
     * @return \Magento\User\Model\User|null
     */
    private function getUser()
    {
        return $this->session->getUser();
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $res = $this->authy->verifyOneTouch($this->getUser());
            if ($res == 'approved') {
                $this->trustedManager->handleTrustDeviceRequest(Authy::CODE, $this->getRequest());
                $this->tfaSession->grantAccess();
                $res = ['success' => true, 'status' => 'approved'];
            } else {
                $res = ['success' => false, 'status' => $res];

                if ($res == 'denied') {
                    $this->event->dispatch('msp_securitysuite_event', [
                        'level' => 'warning',
                        'module' => 'MSP_TwoFactorAuth',
                        'message' => 'Authy onetouch auth denied',
                        'username' => $this->getUser()->getUserName(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            $result->setHttpResponseCode(500);
            $res = ['success' => false, 'message' => $e->getMessage()];

            $this->event->dispatch('msp_securitysuite_event', [
                'level' => 'error',
                'module' => 'MSP_TwoFactorAuth',
                'message' => 'Authy onetouch error',
                'username' => $this->getUser()->getUserName(),
                'additional' => $e->getMessage(),
            ]);
        }

        $result->setData($res);
        return $result;
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return
            $this->tfa->getProviderIsAllowed($this->getUser(), Authy::CODE) &&
            $this->tfa->getProvider(Authy::CODE)->isActive($this->getUser());
    }
}
