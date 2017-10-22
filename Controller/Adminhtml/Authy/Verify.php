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
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy;

class Verify extends Action
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
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Action\Context $context,
        Session $session,
        TfaInterface $tfa,
        Registry $registry,
        UserConfigManagerInterface $userConfigManager,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->tfa = $tfa;
        $this->userConfigManager = $userConfigManager;
        $this->registry = $registry;
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
     * Get verify information
     * @return verify payload
     */
    private function getVerifyInformation()
    {
        $providerConfig = $this->userConfigManager->getProviderConfig($this->getUser()->getId(), Authy::CODE);
        if (!isset($providerConfig['verify'])) {
            return null;
        }

        return $providerConfig['verify'];
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $verifyInfo = $this->getVerifyInformation();
        $this->registry->register('msp_tfa_authy_verify', $verifyInfo);

        return $this->pageFactory->create();
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
            $this->tfa->getProviderIsAllowed($user->getId(), Authy::CODE) &&
            $this->getVerifyInformation() &&
            !$this->tfa->getProvider(Authy::CODE)->isActive($user->getId());
    }
}
