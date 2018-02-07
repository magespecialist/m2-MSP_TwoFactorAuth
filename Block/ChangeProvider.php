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
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\User;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\ProviderInterface;

class ChangeProvider extends Template
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
     * ChangeProvider constructor.
     * @param Template\Context $context
     * @param Session $session
     * @param TfaInterface $tfa
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        TfaInterface $tfa,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->tfa = $tfa;
        $this->session = $session;

        if (!isset($data['provider'])) {
            throw new \InvalidArgumentException('A provider must be specified');
        }
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $providers = [];
        foreach ($this->getProvidersList() as $provider) {
            $providers[] = [
                'code' => $provider->getCode(),
                'name' => $provider->getName(),
                'auth' => $this->getUrl($provider->getAuthAction()),
                'icon' => $this->getViewFileUrl($provider->getIcon()),
            ];
        }

        $this->jsLayout['components']['msp-twofactorauth-change-provider']['switchIcon'] =
            $this->getViewFileUrl('MSP_TwoFactorAuth::images/change_provider.png');
        $this->jsLayout['components']['msp-twofactorauth-change-provider']['providers'] = $providers;

        return parent::getJsLayout();
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
     * Get a list of available providers
     * @return ProviderInterface[]
     */
    private function getProvidersList()
    {
        $res = [];

        $providers = $this->tfa->getUserProviders($this->getUser()->getId());
        foreach ($providers as $provider) {
            if ($provider->getCode() != $this->getData('provider')) {
                $res[] = $provider;
            }
        }

        return $res;
    }
}
