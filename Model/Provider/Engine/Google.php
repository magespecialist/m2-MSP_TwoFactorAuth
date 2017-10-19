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
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\EngineInterface;
use Base32\Base32;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class Google implements EngineInterface
{
    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/google/enabled';
    const XML_PATH_ALLOW_TRUSTED_DEVICES = 'msp_securitysuite_twofactorauth/google/allow_trusted_devices';
    const CODE = 'google'; // Must be the same as defined in di.xml

    private $totp = null;

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
     * Generate random secret
     * @return string
     */
    private function generateSecret()
    {
        $secret = random_bytes(128);
        return preg_replace('/[^A-Za-z0-9]/', '', Base32::encode($secret));
    }

    /**
     * Get TOTP object
     * @param UserInterface $user
     * @return \OTPHP\TOTP
     */
    private function getTotp(UserInterface $user)
    {
        if ($this->totp === null) {
            $config = $this->configManager->getProviderConfig($user, static::CODE);

            // @codingStandardsIgnoreStart
            $this->totp = new \OTPHP\TOTP(
                $user->getEmail(),
                $config['secret']
            );
            // @codingStandardsIgnoreEnd
        }

        return $this->totp;
    }

    /**
     * Get TFA provisioning URL
     * @param UserInterface $user
     * @return string
     */
    private function getProvisioningUrl(UserInterface $user)
    {
        $config = $this->configManager->getProviderConfig($user, static::CODE);
        if (!isset($config['secret'])) {
            $config['secret'] = $this->generateSecret();
            $this->configManager->setProviderConfig($user, static::CODE, $config);
        }

        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        // @codingStandardsIgnoreStart
        $issuer = parse_url($baseUrl, PHP_URL_HOST);
        // @codingStandardsIgnoreEnd

        $totp = $this->getTotp($user);
        $totp->setIssuer($issuer);

        return $totp->getProvisioningUri(true);
    }

    /**
     * Return true on token validation
     * @param UserInterface $user
     * @param DataObject $request
     * @return bool
     */
    public function verify(UserInterface $user, DataObject $request)
    {
        $token = $request->getData('tfa_code');

        $totp = $this->getTotp($user);
        $totp->now();

        return $totp->verify($token);
    }

    /**
     * Render TFA QrCode
     * @param UserInterface $user
     * @return string
     */
    public function getQrCodeAsPng(UserInterface $user)
    {
        // @codingStandardsIgnoreStart
        $qrCode = new QrCode($this->getProvisioningUrl($user));
        $qrCode->setSize(400);
        $qrCode->setErrorCorrectionLevel('high');
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qrCode->setLabelFontSize(16);
        $qrCode->setEncoding('UTF-8');

        $writer = new PngWriter();
        $pngData = $writer->writeString($qrCode);
        // @codingStandardsIgnoreEnd

        return $pngData;
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
}
