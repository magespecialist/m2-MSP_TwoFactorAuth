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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\DeploymentConfig\Reader as DeploymentConfigReader;
use MSP\TwoFactorAuth\Model\ResourceModel\Trusted as TrustedResourceModel;
use MSP\TwoFactorAuth\Api\Data\TrustedInterface;
use MSP\TwoFactorAuth\Api\Data\TrustedInterfaceFactory;
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

    /**
     * @var TrustedInterfaceFactory
     */
    private $trustedInterfaceFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TrustedResourceModel
     */
    private $trustedResourceModel;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var DeploymentConfigReader
     */
    private $deploymentConfigReader;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var TrustedResourceModel\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Session $session,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        DateTime $dateTime,
        TrustedInterfaceFactory $trustedInterfaceFactory,
        TrustedResourceModel $trustedResourceModel,
        RemoteAddress $remoteAddress,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        DeploymentConfigReader $deploymentConfigReader,
        SessionManagerInterface $sessionManager,
        TrustedResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->trustedInterfaceFactory = $trustedInterfaceFactory;
        $this->dateTime = $dateTime;
        $this->request = $request;
        $this->trustedResourceModel = $trustedResourceModel;
        $this->remoteAddress = $remoteAddress;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->deploymentConfigReader = $deploymentConfigReader;
        $this->sessionManager = $sessionManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get device name
     * @return string
     */
    private function getDeviceName()
    {
        $browser = parse_user_agent();
        return $browser['platform'] . ' ' . $browser['browser'] . ' ' . $browser['version'];
    }

    /**
     * Return true if enabled
     * @return bool
     */
    public function getEnabled()
    {
        return (bool) $this->scopeConfig->getValue(TfaInterface::XML_PATH_ENABLED);
    }

    /**
     * Return true if trusted devices are allowed
     * @return bool
     */
    public function getAllowTrustedDevices()
    {
        return (bool) $this->scopeConfig->getValue(TfaInterface::XML_PATH_ALLOW_TRUSTED_DEVICES);
    }

    /**
     * Return true if users are forced to use tfa
     * @return bool
     */
    public function getForceAllUsers()
    {
        return (bool) $this->scopeConfig->getValue(TfaInterface::XML_PATH_FORCE_ALL_USERS);
    }

    /**
     * Return a list of trusted devices for given user id
     * @param int $userId
     * @return array
     */
    public function getTrustedDevices($userId)
    {
        /** @var $collection TrustedResourceModel\Collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('user_id', $userId);

        return $collection->getItems();
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

        return (
            ($this->getUser()->getMspTfaEnabled() || $this->getForceAllUsers()) &&
            !$this->getUserTfaIsActive()
        );
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

        $writer = new PngWriter();
        $pngData = $writer->writeString($qrCode);

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
            ->setMspTfaEnabled(true)
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

    /**
     * Trust device and return secret token
     * @return void
     */
    public function trustDevice()
    {
        $token = md5(uniqid(time()));

        /** @var $trustEntry TrustedInterface */
        $trustEntry = $this->trustedInterfaceFactory->create();
        $trustEntry
            ->setToken($token)
            ->setDateTime($this->dateTime->date())
            ->setUserId($this->getUser()->getId())
            ->setLastIp($this->remoteAddress->getRemoteAddress())
            ->setDeviceName($this->getDeviceName())
            ->setUserAgent($this->request->getServer('HTTP_USER_AGENT'));

        $this->trustedResourceModel->save($trustEntry);

        $this->sendTokenCookie($token);
    }

    /**
     * Send token as cookie
     * @param string $token
     */
    private function sendTokenCookie($token)
    {
        // Enable cookie
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDurationOneYear()
            ->setHttpOnly(true)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());

        $this->cookieManager->setPublicCookie(TfaInterface::TRUSTED_DEVICE_COOKIE, $token, $cookieMetadata);
    }

    /**
     * Rotate secret trust token
     * @return void
     */
    public function rotateToken()
    {
        $token = $this->cookieManager->getCookie(TfaInterface::TRUSTED_DEVICE_COOKIE);

        /** @var $trustEntry TrustedInterface */
        $trustEntry = $this->trustedInterfaceFactory->create();
        $this->trustedResourceModel->load($trustEntry, $token, TrustedInterface::TOKEN);
        if ($trustEntry->getId()) {
            $token = md5(uniqid(time()));

            $trustEntry->setToken($token);
            $this->trustedResourceModel->save($trustEntry);

            $this->sendTokenCookie($token);
        }
    }

    /**
     * Return true if device is trusted
     * @return bool
     */
    public function isTrustedDevice()
    {
        $token = $this->cookieManager->getCookie(TfaInterface::TRUSTED_DEVICE_COOKIE);

        /** @var $trustEntry TrustedInterface */
        $trustEntry = $this->trustedInterfaceFactory->create();
        $this->trustedResourceModel->load($trustEntry, $token, TrustedInterface::TOKEN);

        return $trustEntry->getId() && ($trustEntry->getUserId() == $this->getUser()->getId());
    }

    /**
     * Revoke trusted device
     * @param int $tokenId
     * @return void
     */
    public function revokeTrustedDevice($tokenId)
    {
        $trustEntry = $this->trustedInterfaceFactory->create();
        $this->trustedResourceModel->load($trustEntry, $tokenId);
        $this->trustedResourceModel->delete($trustEntry);
    }
}
