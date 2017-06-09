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

use Base32\Base32;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use Magento\Backend\Model\Auth\Session;

class Tfa implements TfaInterface
{
    protected $_totp = null;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Session $session,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return true if enabled
     * @return bool
     */
    public function getEnabled()
    {
        return (bool) $this->scopeConfig->getValue(TfaInterface::XML_PATH_GENERAL_ENABLED);
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

        if (is_null($this->_totp)) {
            $this->_totp = new \OTPHP\TOTP(
                $user->getEmail(),
                $user->getMspTfaSecret()
            );
        }

        return $this->_totp;
    }

    /**
     * Return true if user must activate his TFA
     * @return bool
     */
    public function getUserMustActivateTfa()
    {
        if (!$this->getEnabled()) {
            return false;
        }

        return ($this->getUser()->getMspTfaEnabled() && !$this->getUserTfaIsActive());
    }

    /**
     * Return true if user must authenticate via TFA
     * @return bool
     */
    public function getUserMustAuth()
    {
        if (!$this->getEnabled()) {
            return false;
        }

        if (!$this->getUserTfaIsActive()) {
            return false;
        }

        return !$this->getTwoAuthFactorPassed();
    }

    /**
     * Return true if user has TFA activated
     * @return bool
     */
    public function getUserTfaIsActive()
    {
        if (!$this->getEnabled()) {
            return false;
        }

        $user = $this->getUser();

        return ($user->getMspTfaEnabled() && $user->getMspTfaSecret() && $user->getMspTfaActivated());
    }

    /**
     * Get TFA provisioning URL
     * @return string
     */
    public function getProvisioningUrl()
    {
        $user = $this->getUser();

        if (!$user) {
            return null;
        }

        if (!$user->getMspTfaSecret()) {
            $secret = $this->generateSecret();

            $user
                ->setMspTfaSecret($secret)
                ->save();
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
     * @param $token
     * @return bool
     */
    public function verify($token)
    {
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

        $writer = new PngWriter($qrCode);
        $pngData = $writer->writeString();

        return $pngData;
    }

    /**
     * Activate user TFA
     * @return TfaInterface
     * @throws \Exception
     */
    public function activateUserTfa()
    {
        $user = $this->getUser();
        if (!$user) {
            return $this;
        }

        if (!$user->getMspTfaSecret()) {
            throw new \Exception('Cannot activate user due to missing secret code');
        }

        $user
            ->setMspTfaActivated(true)
            ->save();

        return $this;
    }

    /**
     * Set TFA pass status
     * @param $status
     * @return TfaInterface
     */
    public function setTwoAuthFactorPassed($status)
    {
        $this->session->setMspTfaPassed($status);
        return $this;
    }

    /**
     * Get TFA pass status
     * @return bool
     */
    public function getTwoAuthFactorPassed()
    {
        return $this->session->getMspTfaPassed();
    }
}
