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
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\EngineInterface;

class DuoSecurity implements EngineInterface
{
    const CODE = 'duo_security'; // Must be the same as defined in di.xml

    const DUO_PREFIX = "TX";
    const APP_PREFIX = "APP";
    const AUTH_PREFIX = "AUTH";

    const DUO_EXPIRE = 300;
    const APP_EXPIRE = 3600;

    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/duo/enabled';
    const XML_PATH_INTEGRATION_KEY = 'msp_securitysuite_twofactorauth/duo/integration_key';
    const XML_PATH_SECRET_KEY = 'msp_securitysuite_twofactorauth/duo/secret_key';
    const XML_PATH_API_HOSTNAME = 'msp_securitysuite_twofactorauth/duo/api_hostname';
    const XML_PATH_APPLICATION_KEY = 'msp_securitysuite_twofactorauth/duo/application_key';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * DuoSecurity constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get API hostname
     * @return string
     */
    public function getApiHostname()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_API_HOSTNAME);
    }

    /**
     * Get application key
     * @return string
     */
    private function getApplicationKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_APPLICATION_KEY);
    }

    /**
     * Get secret key
     * @return string
     */
    private function getSecretKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_SECRET_KEY);
    }

    /**
     * Get integration key
     * @return string
     */
    private function getIntegrationKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_INTEGRATION_KEY);
    }

    /**
     * Sign values
     * @param string $key
     * @param string $values
     * @param string $prefix
     * @param int $expire
     * @param int $time
     * @return string
     */
    private function signValues($key, $values, $prefix, $expire, $time)
    {
        $exp = $time + $expire;
        $cookie = $prefix . '|' . base64_encode($values . '|' . $exp);

        $sig = hash_hmac("sha1", $cookie, $key);
        return $cookie . '|' . $sig;
    }

    /**
     * Parse signed values and return username
     * @param string $key
     * @param string $val
     * @param string $prefix
     * @param int $time
     * @return string|false
     */
    private function parseValues($key, $val, $prefix, $time)
    {
        $integrationKey = $this->getIntegrationKey();

        $timestamp = ($time ? $time : time());

        $parts = explode('|', $val);
        if (count($parts) !== 3) {
            return false;
        }
        list($uPrefix, $uB64, $uSig) = $parts;

        $sig = hash_hmac("sha1", $uPrefix . '|' . $uB64, $key);
        if (hash_hmac("sha1", $sig, $key) !== hash_hmac("sha1", $uSig, $key)) {
            return false;
        }

        if ($uPrefix !== $prefix) {
            return false;
        }

        // @codingStandardsIgnoreStart
        $cookieParts = explode('|', base64_decode($uB64));
        // @codingStandardsIgnoreEnd

        if (count($cookieParts) !== 3) {
            return false;
        }
        list($user, $uIkey, $exp) = $cookieParts;

        if ($uIkey !== $integrationKey) {
            return false;
        }
        if ($timestamp >= (int) $exp) {
            return false;
        }

        return $user;
    }

    /**
     * Get request signature
     * @param UserInterface $user
     * @return string
     */
    public function getRequestSignature(UserInterface $user)
    {
        $time = time();

        $values = $user->getUserName() . '|' . $this->getIntegrationKey();
        $duoSignature = $this->signValues(
            $this->getSecretKey(),
            $values,
            static::DUO_PREFIX,
            static::DUO_EXPIRE,
            $time
        );
        $appSignature = $this->signValues(
            $this->getApplicationKey(),
            $values,
            static::APP_PREFIX,
            static::APP_EXPIRE,
            $time
        );

        return $duoSignature . ':' . $appSignature;
    }

    /**
     * Return true on token validation
     * @param UserInterface $user
     * @param DataObject $request
     * @return bool
     */
    public function verify(UserInterface $user, DataObject $request)
    {
        $time = time();

        list($authSig, $appSig) = explode(':', $request->getData('sig_response'));

        $authUser = $this->parseValues($this->getSecretKey(), $authSig, static::AUTH_PREFIX, $time);
        $appUser = $this->parseValues($this->getApplicationKey(), $appSig, static::APP_PREFIX, $time);

        return (($authUser === $appUser) && ($appUser === $user->getUserName()));
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled()
    {
        return
            !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED) &&
            !!$this->getApiHostname() &&
            !!$this->getIntegrationKey() &&
            !!$this->getApiHostname() &&
            !!$this->getSecretKey();
    }

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function isTrustedDevicesAllowed()
    {
        return false;
    }
}
