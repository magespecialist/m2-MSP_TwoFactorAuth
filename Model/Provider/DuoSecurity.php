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

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use MSP\TwoFactorAuth\Api\ProviderInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class DuoSecurity implements ProviderInterface
{
    const CODE = 'duo_security';

    const DUO_PREFIX = "TX";
    const APP_PREFIX = "APP";
    const AUTH_PREFIX = "AUTH";

    const DUO_EXPIRE = 300;
    const APP_EXPIRE = 3600;

    const XML_PATH_ENABLED = 'msp_securitysuite/twofactorauth_duo/enabled';
    const XML_PATH_INTEGRATION_KEY = 'msp_securitysuite/twofactorauth_duo/integration_key';
    const XML_PATH_SECRET_KEY = 'msp_securitysuite/twofactorauth_duo/secret_key';
    const XML_PATH_API_HOSTNAME = 'msp_securitysuite/twofactorauth_duo/api_hostname';
    const XML_PATH_APPLICATION_KEY = 'msp_securitysuite/twofactorauth_duo/application_key';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $writer,
        Session $session
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->writer = $writer;
        $this->session = $session;
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
     * Generate an application key
     */
    public function generateApplicationKey()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $this->writer->save(static::XML_PATH_APPLICATION_KEY, $randomString);
    }

    /**
     * Get application key
     * @return string
     */
    protected function getApplicationKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_APPLICATION_KEY);
    }

    /**
     * Get secret key
     * @return string
     */
    protected function getSecretKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_SECRET_KEY);
    }

    /**
     * Get integration key
     * @return string
     */
    protected function getIntegrationKey()
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
    protected function signValues($key, $values, $prefix, $expire, $time)
    {
        $exp = $time + $expire;
        $cookie = $prefix . '|' . base64_encode($values . '|' . $exp);

        $sig = hash_hmac("sha1", $cookie, $key);
        return $cookie . '|' . $sig;
    }

    /**
     * Get current admin user
     * @return \Magento\User\Model\User
     */
    protected function getUser()
    {
        return $this->session->getUser();
    }

    /**
     * Get request signature
     * @return string
     */
    public function getRequestSignature()
    {
        $user = $this->getUser();
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
     * Parse signed values and return username
     * @param string $key
     * @param string $val
     * @param string $prefix
     * @param int $time
     * @return string|false
     */
    protected function parseValues($key, $val, $prefix, $time)
    {
        $integrationKey = $this->getIntegrationKey();

        $ts = ($time ? $time : time());

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

        $cookie_parts = explode('|', base64_decode($uB64));
        if (count($cookie_parts) !== 3) {
            return false;
        }
        list($user, $uIkey, $exp) = $cookie_parts;

        if ($uIkey !== $integrationKey) {
            return false;
        }
        if ($ts >= intval($exp)) {
            return false;
        }

        return $user;
    }

    /**
     * Verify DUO response
     * @param \Magento\Framework\App\RequestInterface $request
     * @return boolean
     */
    public function verify(\Magento\Framework\App\RequestInterface $request)
    {
        $time = time();

        list($authSig, $appSig) = explode(':', $request->getParam('sig_response'));

        $authUser = $this->parseValues($this->getSecretKey(), $authSig, static::AUTH_PREFIX, $time);
        $appUser = $this->parseValues($this->getApplicationKey(), $appSig, static::APP_PREFIX, $time);

        return (($authUser === $appUser) && ($appUser === $this->getUser()->getUserName()));
    }

    /**
     * Get provider name
     * @return string
     */
    public function getName()
    {
        return __('Duo Security');
    }
    /**
     * Return true if this provider is enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return
            !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED) &&
            $this->getSecretKey() &&
            $this->getIntegrationKey() &&
            $this->getApiHostname();
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
        return 'msp_twofactorauth/duo/auth';
    }

    /**
     * Return auth action
     * @return string
     */
    public function getAuthPath()
    {
        return 'msp_twofactorauth/duo/auth';
    }

    /**
     * Return true if user has a full configuration
     * @param \Magento\User\Model\User $user
     * @return boolean
     */
    public function getUserIsConfigured(\Magento\User\Model\User $user)
    {
        return true;
    }

    /**
     * Return true if allow trusted devices
     * @return boolean
     */
    public function allowTrustedDevices()
    {
        return false;
    }
}
