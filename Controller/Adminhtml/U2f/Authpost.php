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
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Controller\Adminhtml\U2f;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObjectFactory;
use MSP\SecuritySuiteCommon\Api\SecuritySuiteInterface;
use MSP\TwoFactorAuth\Api\TfaSessionInterface;
use MSP\TwoFactorAuth\Api\TrustedManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\U2fKey;
use MSP\TwoFactorAuth\Model\Tfa;
use Magento\Framework\Event\ManagerInterface as EventInterface;

/**
 * Class Authpost
 * @package MSP\TwoFactorAuth\Controller\Adminhtml\U2f
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class Authpost extends Action
{
    /**
     * @var Tfa
     */
    private $tfa;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var U2fKey
     */
    private $u2fKey;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

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

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        Tfa $tfa,
        Session $session,
        JsonFactory $jsonFactory,
        TfaSessionInterface $tfaSession,
        TrustedManagerInterface $trustedManager,
        U2fKey $u2fKey,
        DataObjectFactory $dataObjectFactory,
        Action\Context $context
    ) {
        parent::__construct($context);

        $this->tfa = $tfa;
        $this->session = $session;
        $this->u2fKey = $u2fKey;
        $this->jsonFactory = $jsonFactory;
        $this->tfaSession = $tfaSession;
        $this->trustedManager = $trustedManager;
        $this->event = $context->getEventManager();
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $this->u2fKey->verify($this->getUser(), $this->dataObjectFactory->create([
                'data' => $this->getRequest()->getParams(),
            ]));
            $this->tfaSession->grantAccess();
            $this->trustedManager->handleTrustDeviceRequest(U2fKey::CODE, $this->getRequest());

            $res = ['success' => true];
        } catch (\Exception $e) {
            $this->event->dispatch(SecuritySuiteInterface::EVENT, [
                'level' => 'error',
                'module' => 'MSP_TwoFactorAuth',
                'message' => 'U2F error',
                'username' => $this->getUser()->getUserName(),
                'additional' => $e->getMessage(),
            ]);

            $res = ['success' => false, 'message' => $e->getMessage()];
        }

        $result->setData($res);
        return $result;
    }

    /**
     * @return \Magento\User\Model\User|null
     */
    private function getUser()
    {
        return $this->session->getUser();
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
            $this->tfa->getProviderIsAllowed($user->getId(), U2fKey::CODE) &&
            $this->tfa->getProvider(U2fKey::CODE)->isActive($user->getId());
    }
}
