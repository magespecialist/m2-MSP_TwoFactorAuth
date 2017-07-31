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

namespace MSP\TwoFactorAuth\Model\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use MSP\TwoFactorAuth\Api\ProviderInterface;

class Authy implements ProviderInterface
{
    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/authy/enabled';
    const CODE = 'authy';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get provider name
     * @return string
     */
    public function getName()
    {
        return __('Authy');
    }

    /**
     * Get provider code
     * @return string
     */
    public function getCode()
    {
        return static::CODE;
    }

    /**
     * Return true if this provider is enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED);
    }

    /**
     * Return a list of reserved actions accessible without 2FA
     * @return array
     */
    public function getAllowedExtraActions()
    {
        return [];
    }

    /**
     * Return activation action
     * @return string
     */
    public function getActivatePath()
    {
        return 'msp_twofactorauth/authy/activate';
    }

    /**
     * Return auth action
     * @return string
     */
    public function getAuthPath()
    {
        // TODO: Implement getAuthPath() method.
    }

    /**
     * Return true if user has a full configuration
     * @param \Magento\User\Model\User $user
     * @return boolean
     */
    public function getUserIsConfigured(\Magento\User\Model\User $user)
    {
        return false;
    }

    /**
     * Verify auth
     * @param \Magento\Framework\App\RequestInterface $request
     * @return boolean
     */
    public function verify(\Magento\Framework\App\RequestInterface $request)
    {
        // TODO: Implement verify() method.
    }

    /**
     * Return true if allow trusted devices
     * @return boolean
     */
    public function allowTrustedDevices()
    {
        return true;
    }
}
