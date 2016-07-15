<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_TwoFactorAuth
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\TwoFactorAuth\Model;

use Base32\Base32;
use Endroid\QrCode\Factory\QrCodeFactory;
use Magento\Store\Model\StoreManagerInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use Magento\Backend\Model\Auth\Session;
use MSP\TwoFactorAuth\Helper\Data;

class Tfa implements TfaInterface
{
    protected $session;
    protected $helperData;
    protected $qrCodeFactory;
    protected $storeManagerInterface;
    protected $_totp = null;

    public function __construct(
        Session $session,
        Data $helperData,
        QrCodeFactory $qrCodeFactory,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->session = $session;
        $this->helperData = $helperData;
        $this->qrCodeFactory = $qrCodeFactory;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    /**
     * Get current admin user
     * @return \Magento\User\Model\User|null
     */
    protected function _getUser()
    {
        return $this->session->getUser();
    }

    /**
     * Generate random secret
     * @return string
     */
    protected function _generateSecret()
    {
        $secret = mcrypt_create_iv(128, MCRYPT_RAND);
        return Base32::encode($secret);
    }

    /**
     * Get TOTP object
     * @return \OTPHP\TOTP
     */
    protected function _getTotp()
    {
        $user = $this->_getUser();

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
        if (!$this->helperData->getEnabled()) {
            return false;
        }

        return ($this->_getUser()->getMspTfaEnabled() && !$this->getUserTfaIsActive());
    }

    /**
     * Return true if user must authenticate via TFA
     * @return bool
     */
    public function getUserMustAuth()
    {
        if (!$this->helperData->getEnabled()) {
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
        if (!$this->helperData->getEnabled()) {
            return false;
        }

        $user = $this->_getUser();

        return ($user->getMspTfaEnabled() && $user->getMspTfaSecret() && $user->getMspTfaActivated());
    }

    /**
     * Get TFA provisioning URL
     * @return string
     */
    public function getProvisioningUrl()
    {
        $user = $this->_getUser();

        if (!$user) {
            return null;
        }

        if (!$user->getMspTfaSecret()) {
            $secret = $this->_generateSecret();

            $user
                ->setMspTfaSecret($secret)
                ->save();
        }

        $baseUrl = $this->storeManagerInterface->getStore()->getBaseUrl();

        // @codingStandardsIgnoreStart
        $issuer = parse_url($baseUrl, PHP_URL_HOST);
        // @codingStandardsIgnoreEnd

        $totp = $this->_getTotp();
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
        $totp = $this->_getTotp();
        $totp->now();

        return $totp->verify($token);
    }

    /**
     * Render TFA QrCode
     */
    public function renderQrCode()
    {
        $qrCode = $this->qrCodeFactory->createQrCode([
            'text' => $this->getProvisioningUrl(),
            'size' => 400,
            'padding' => 10,
            'error_correction_level' => 'high',
            'foreground_color' => ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0],
            'background_color' => ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0],
            'label_font_size' => 16,
            'extension' => 'png',
        ]);

        $qrCode->render(null, 'png');
    }

    /**
     * Activate user TFA
     * @return TfaInterface
     * @throws \Exception
     */
    public function activateUserTfa()
    {
        $user = $this->_getUser();
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
