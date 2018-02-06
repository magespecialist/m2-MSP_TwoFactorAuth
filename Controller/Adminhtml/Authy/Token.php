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
use MSP\TwoFactorAuth\Controller\Adminhtml\AbstractAction;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Token extends AbstractAction
{
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
     * @var Authy\Token
     */
    private $token;

    /**
     * Token constructor.
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param TfaInterface $tfa
     * @param Authy\Token $token
     * @param Session $session
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        TfaInterface $tfa,
        Authy\Token $token,
        Session $session
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->jsonFactory = $jsonFactory;
        $this->tfa = $tfa;
        $this->token = $token;
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
        $via = $this->getRequest()->getParam('via');
        $result = $this->jsonFactory->create();

        try {
            $this->token->request($this->getUser(), $via);
            $res = ['success' => true];
        } catch (\Exception $e) {
            $result->setHttpResponseCode(500);
            $res = ['success' => false, 'message' => $e->getMessage()];
        }

        $result->setData($res);
        return $result;
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
            $this->tfa->getProvider(Authy::CODE)->isActive($user->getId());
    }
}
