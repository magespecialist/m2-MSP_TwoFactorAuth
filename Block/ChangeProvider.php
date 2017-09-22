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

namespace MSP\TwoFactorAuth\Block;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Registry;
use Magento\User\Model\User;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Model\ProviderInterface;

class ChangeProvider extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Template\Context $context,
        Session $session,
        TfaInterface $tfa,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->tfa = $tfa;
        $this->session = $session;
    }

    /**
     * Get user
     * @return User|null
     */
    private function getUser()
    {
        return $this->session->getUser();
    }

    /**
     * Get current 2FA provider if defined
     * @return string|null
     */
    public function getCurrentProviderCode()
    {
        return $this->registry->registry('msp_tfa_current_provider');
    }

    /**
     * Return true if current provider is active
     * @return bool
     */
    public function isCurrentProviderActive()
    {
        $currentProvider = $this->tfa->getProvider($this->getCurrentProviderCode());
        return $currentProvider->isActive($this->getUser());
    }

    /**
     * Get a list of available providers
     * @return ProviderInterface[]
     */
    public function getProvidersList()
    {
        $res = [];

        $providers = $this->tfa->getUserProviders($this->getUser());
        foreach ($providers as $provider) {
            if ($provider->getCode() != $this->getCurrentProviderCode()) {
                $res[] = $provider;
            }
        }

        return $res;
    }
}
