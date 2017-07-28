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

namespace MSP\TwoFactorAuth\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use MSP\TwoFactorAuth\Api\ProviderConfigInterface;
use MSP\TwoFactorAuth\Api\ProviderInterface;
use MSP\TwoFactorAuth\Api\ProviderManagementInterface;

class ProviderManagement implements ProviderManagementInterface
{
    /**
     * @var array
     */
    private $providers;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var ProviderConfigInterface
     */
    private $providerConfig;

    public function __construct(
        Session $session,
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        ProviderConfigInterface $providerConfig,
        $providers = []
    ) {
        $this->session = $session;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->providerConfig = $providerConfig;
        $this->providers = [];

        foreach ($providers as $provider) {
            $this->providers[$provider->getCode()] = $provider;
        }
    }

    /**
     * Get current admin user
     * @return \Magento\User\Model\User|null
     */
    protected function getUser()
    {
        return $this->session->getUser();
    }

    /**
     * Return a providers list
     * @return ProviderInterface[]
     */
    public function getAllProviders()
    {
        return $this->providers;
    }

    /**
     * Return a provider by its code
     * @param string $code
     * @return ProviderInterface
     * @throws NoSuchEntityException
     */
    public function getProvider($code)
    {
        if (!isset($this->providers[$code])) {
            throw new NoSuchEntityException(__('2FA provider with code %1 does not exist', $code));
        }

        return $this->providers[$code];
    }

    /**
     * Get user's provider
     * @param \Magento\User\Model\User $user = null
     * @return ProviderInterface|null
     */
    public function getUserProvider(\Magento\User\Model\User $user = null)
    {
        if (is_null($user)) {
            $user = $this->getUser();
        }

        if (!$user) {
            return null;
        }

        if ($user->getMspTfaProvider() == ProviderManagementInterface::PROVIDER_NONE) {
            return null;
        }

        try {
            return $this->getProvider($user->getMspTfaProvider());
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Save user TFA config
     * @param array $config
     * @param string $providerCode
     * @param \Magento\User\Model\User $user = null
     * @return array
     * @throws LocalizedException
     */
    public function setUserProviderConfiguration($config, $providerCode, $user = null)
    {
        if (is_null($user)) {
            $user = $this->getUser();
        }

        return $this->providerConfig->setUserProviderConfiguration($config, $providerCode, $user);
    }

    /**
     * Get user TFA config
     * @param string $providerCode
     * @param \Magento\User\Model\User $user = null
     * @return array
     */
    public function getUserProviderConfiguration($providerCode, $user = null)
    {
        if (is_null($user)) {
            $user = $this->getUser();
        }

        return $this->providerConfig->getUserProviderConfiguration($providerCode, $user);
    }
}
