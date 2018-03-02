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

namespace MSP\TwoFactorAuth\Controller\Adminhtml\Tfa;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Controller\Adminhtml\AbstractAction;

class Index extends AbstractAction
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
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var Action\Context
     */
    private $context;

    public function __construct(
        Action\Context $context,
        Session $session,
        UserConfigManagerInterface $userConfigManager,
        TfaInterface $tfa
    ) {
        parent::__construct($context);
        $this->tfa = $tfa;
        $this->session = $session;
        $this->userConfigManager = $userConfigManager;
        $this->context = $context;
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
        $user = $this->getUser();

        $providersToConfigure = $this->tfa->getProvidersToActivate($user->getId());
        if (!empty($providersToConfigure)) {
            return $this->_redirect($providersToConfigure[0]->getConfigureAction());
        }

        $providerCode = '';

        $defaultProviderCode = $this->userConfigManager->getDefaultProvider($user->getId());
        if ($this->tfa->getProviderIsAllowed($user->getId(), $defaultProviderCode)) {
            $providerCode = $defaultProviderCode;
        }

        if (!$providerCode) {
            $providers = $this->tfa->getUserProviders($user->getId());
            if (!empty($providers)) {
                $providerCode = $providers[0]->getCode();
            }
        }

        if (!$providerCode) {
            return $this->_redirect($this->context->getBackendUrl()->getStartupPageUrl());
        }

        if ($provider = $this->tfa->getProvider($providerCode)) {
            return $this->_redirect($provider->getAuthAction());
        }

        throw new LocalizedException(__('Internal error accessing 2FA index page'));
    }
}
