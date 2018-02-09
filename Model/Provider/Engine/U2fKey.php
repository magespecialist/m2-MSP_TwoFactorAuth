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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Api\EngineInterface;
use u2flib_server\U2F;

class U2fKey implements EngineInterface
{
    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/u2fkey/enabled';
    const XML_PATH_ALLOW_TRUSTED_DEVICES = 'msp_securitysuite_twofactorauth/u2fkey/allow_trusted_devices';
    const CODE = 'u2fkey'; // Must be the same as defined in di.xml

    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * U2fKey constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param UserConfigManagerInterface $userConfigManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        UserConfigManagerInterface $userConfigManager
    ) {
        $this->userConfigManager = $userConfigManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Converts array to object
     * @param array $hash
     * @return \stdClass
     */
    private function hashToObject(array $hash)
    {
        // @codingStandardsIgnoreStart
        $object = new \stdClass();
        // @codingStandardsIgnoreEnd
        foreach ($hash as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    /**
     * Return true on token validation
     * @param UserInterface $user
     * @param DataObject $request
     * @return true
     * @throws LocalizedException
     * @throws \u2flib_server\Error
     */
    public function verify(UserInterface $user, DataObject $request)
    {
        $u2f = $this->getU2f();

        $registration = $this->getRegistration($user);
        if ($registration === null) {
            throw new LocalizedException(__('Missing registration data'));
        }

        $requests = [$this->hashToObject($request->getData('request')[0])];
        $registrations = [$this->hashToObject($registration)];
        $response = $this->hashToObject($request->getData('response'));

        // it triggers an error in case of auth failure
        $u2f->doAuthenticate($requests, $registrations, $response);
        return true;
    }

    /**
     * Create the registration challenge
     * @return array
     * @throws LocalizedException
     * @throws \u2flib_server\Error
     */
    public function getRegisterData()
    {
        $u2f = $this->getU2f();
        return $u2f->getRegisterData();
    }

    /**
     * Get authenticate data
     * @param UserInterface $user
     * @return array
     * @throws LocalizedException
     * @throws \u2flib_server\Error
     */
    public function getAuthenticateData(UserInterface $user)
    {
        $u2f = $this->getU2f();

        $registration = $this->getRegistration($user);
        if ($registration === null) {
            throw new LocalizedException(__('Missing registration data'));
        }

        return $u2f->getAuthenticateData([$this->hashToObject($registration)]);
    }

    /**
     * Get registration information
     * @param UserInterface $user
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getRegistration(UserInterface $user)
    {
        $providerConfig = $this->userConfigManager->getProviderConfig($user->getId(), static::CODE);

        if (!isset($providerConfig['registration'])) {
            return null;
        }

        return $providerConfig['registration'];
    }

    /**
     * Register a new key
     * @param UserInterface $user
     * @param array $request
     * @param array $response
     * @return \u2flib_server\Registration
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \u2flib_server\Error
     */
    public function registerDevice(UserInterface $user, array $request, array $response)
    {
        // Must convert to object
        $request = $this->hashToObject($request);
        $response = $this->hashToObject($response);

        $u2f = $this->getU2f();
        $res = $u2f->doRegister($request, $response);

        $this->userConfigManager->addProviderConfig($user->getId(), static::CODE, [
            'registration' => [
                'certificate' => $res->certificate,
                'keyHandle' => $res->keyHandle,
                'publicKey' => $res->publicKey,
                'counter' => $res->counter,
            ]
        ]);
        $this->userConfigManager->activateProviderConfiguration($user->getId(), static::CODE);

        return $res;
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled()
    {
        return !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED);
    }

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function isTrustedDevicesAllowed()
    {
        return !!$this->scopeConfig->getValue(static::XML_PATH_ALLOW_TRUSTED_DEVICES);
    }

    /**
     * @return U2F
     * @throws LocalizedException
     * @throws \u2flib_server\Error
     */
    private function getU2f()
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore(Store::ADMIN_CODE);

        $baseUrl = $store->getBaseUrl();
        if (preg_match('/^(https?:\/\/.+?)\//', $baseUrl, $matches)) {
            $domain = $matches[1];
        } else {
            throw new LocalizedException(__('Unexpected error while parsing domain name'));
        }

        /** @var U2F $u2f */
        // @codingStandardsIgnoreStart
        return new U2F($domain);
        // @codingStandardsIgnoreEnd
    }
}
