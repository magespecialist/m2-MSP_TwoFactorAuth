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
 * @package    MSP_NoSpam
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Model\Provider\Engine;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\EngineInterface;
use u2flib_server\U2F;

class U2fKey implements EngineInterface
{
    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/u2fkey/enabled';
    const XML_PATH_ALLOW_TRUSTED_DEVICES = 'msp_securitysuite_twofactorauth/u2fkey/allow_trusted_devices';
    const CODE = 'u2fkey'; // Must be the same as defined in di.xml

    /**
     * @var UserConfigManagerInterface
     */
    private $configManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        UserConfigManagerInterface $configManager
    ) {
        $this->configManager = $configManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return true on token validation
     * @param UserInterface $user
     * @param RequestInterface $request
     * @return bool
     */
    public function verify(UserInterface $user, RequestInterface $request)
    {
        $token = $request->getParam('tfa_code');

        $totp = $this->getTotp($user);
        $totp->now();

        return $totp->verify($token);
    }

    /**
     * Create the registration challenge
     * @return array
     */
    public function getRegisterData()
    {
        $u2f = $this->getU2f();
        return $u2f->getRegisterData();
    }

    /**
     * Create the authentication challenge
     * @param CustomerInterface $customer
     * @return array
     */
    public function getAuthChallenge(CustomerInterface $customer)
    {
       $u2f = $this->getU2f();

       return $u2f->getAuthenticateData($customer->getAuthData());
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function getIsEnabled()
    {
        return !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED);
    }

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function getAllowTrustedDevices()
    {
        return !!$this->scopeConfig->getValue(static::XML_PATH_ALLOW_TRUSTED_DEVICES);
    }

    protected function getU2f()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore(Store::ADMIN_CODE);

        /** @var U2F $u2f */
        return new U2F(trim($store->getBaseUrl(), '/'));
    }
}
