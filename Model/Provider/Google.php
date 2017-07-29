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

use Base32\Base32;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use MSP\TwoFactorAuth\Api\ProviderConfigInterface;
use MSP\TwoFactorAuth\Api\ProviderInterface;

class Google implements ProviderInterface
{
    const XML_PATH_ENABLED = 'msp_securitysuite/twofactorauth_google/enabled';
    const XML_PATH_ALLOW_TRUSTED_DEVICES = 'msp_securitysuite/twofactorauth_google/allow_trusted_devices';
    const CODE = 'google';

    protected $_totp = null;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ProviderConfigInterface
     */
    private $providerConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ProviderConfigInterface $providerConfig,
        Session $session
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->session = $session;
        $this->providerConfig = $providerConfig;
    }

    /**
     * Get provider name
     * @return string
     */
    public function getName()
    {
        return __('Google Authenticator');
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
        return [
            'msp_twofactorauth_google_activatepost',
            'msp_twofactorauth_google_qrcode',
        ];
    }

    /**
     * Return activation action
     * @return string
     */
    public function getActivatePath()
    {
        return 'msp_twofactorauth/google/activate';
    }

    /**
     * Return auth action
     * @return string
     */
    public function getAuthPath()
    {
        return 'msp_twofactorauth/google/auth';
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
     * Generate random secret
     * @return string
     */
    protected function generateSecret()
    {
        $secret = random_bytes(128);
        return preg_replace('/[^A-Za-z0-9]/', '', Base32::encode($secret));
    }

    /**
     * Get TOTP object
     * @return \OTPHP\TOTP
     */
    protected function getTotp()
    {
        $user = $this->getUser();
        $config = $this->providerConfig->getUserProviderConfiguration(static::CODE, $user);

        if (is_null($this->_totp)) {
            $this->_totp = new \OTPHP\TOTP(
                $user->getEmail(),
                $config['secret']
            );
        }

        return $this->_totp;
    }

    /**
     * Get TFA provisioning URL
     * @return string
     */
    protected function getProvisioningUrl()
    {
        $user = $this->getUser();
        if (!$user) {
            return null;
        }

        $config = $this->providerConfig->getUserProviderConfiguration(static::CODE, $user);

        if (!isset($config['secret'])) {
            $config['secret'] = $this->generateSecret();
            $this->providerConfig->setUserProviderConfiguration($config, static::CODE, $user);
        }

        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        // @codingStandardsIgnoreStart
        $issuer = parse_url($baseUrl, PHP_URL_HOST);
        // @codingStandardsIgnoreEnd

        $totp = $this->getTotp();
        $totp->setIssuer($issuer);

        return $totp->getProvisioningUri(true);
    }

    /**
     * Return true on token validation
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function verify(\Magento\Framework\App\RequestInterface $request)
    {
        $token = $request->getParam('tfa_code');

        $totp = $this->getTotp();
        $totp->now();

        return $totp->verify($token);
    }

    /**
     * Render TFA QrCode
     */
    public function getQrCodeAsPng()
    {
        $qrCode = new QrCode($this->getProvisioningUrl());
        $qrCode
            ->setSize(400)
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
            ->setLabelFontSize(16)
            ->setEncoding('UTF-8');

        $writer = new PngWriter();
        $pngData = $writer->writeString($qrCode);

        return $pngData;
    }

    /**
     * Return true if user has a full configuration
     * @param \Magento\User\Model\User $user
     * @return boolean
     */
    public function getUserIsConfigured(\Magento\User\Model\User $user)
    {
        $config = $this->providerConfig->getUserProviderConfiguration(static::CODE, $user);
        return (isset($config['secret']) && $config['secret']);
    }

    /**
     * Return true if allow trusted devices
     * @return boolean
     */
    public function allowTrustedDevices()
    {
        return !!$this->scopeConfig->getValue(static::XML_PATH_ALLOW_TRUSTED_DEVICES);
    }
}
