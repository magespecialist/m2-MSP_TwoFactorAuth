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

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Json\Encoder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\User\Model\User;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TrustedManagerInterface;
use MSP\TwoFactorAuth\Model\ResourceModel\Trusted as TrustedResourceModel;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class TrustedManager implements TrustedManagerInterface
{
    protected $isTrustedDevice = null;

    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var TrustedFactory
     */
    private $trustedFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var Session
     */
    private $session;
    
    /**
     * @var TrustedResourceModel
     */
    private $trustedResourceModel;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var Encoder
     */
    private $encoder;

    /**
     * @var Decoder
     */
    private $decoder;

    public function __construct(
        TfaInterface $tfa,
        DateTime $dateTime,
        Session $session,
        RemoteAddress $remoteAddress,
        Encoder $encoder,
        Decoder $decoder,
        TrustedResourceModel $trustedResourceModel,
        CookieManagerInterface $cookieManager,
        SessionManagerInterface $sessionManager,
        TrustedFactory $trustedFactory,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->tfa = $tfa;
        $this->trustedFactory = $trustedFactory;
        $this->dateTime = $dateTime;
        $this->remoteAddress = $remoteAddress;
        $this->session = $session;
        $this->trustedResourceModel = $trustedResourceModel;
        $this->cookieManager = $cookieManager;
        $this->sessionManager = $sessionManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    /**
     * Get current user
     * @return User|null
     */
    protected function getUser()
    {
        return $this->session->getUser();
    }

    /**
     * Get device name
     * @return string
     */
    protected function getDeviceName()
    {
        $browser = parse_user_agent();
        return $browser['platform'] . ' ' . $browser['browser'] . ' ' . $browser['version'];
    }

    /**
     * Get token collection from cookie
     * @return array
     */
    protected function getTokenCollection()
    {
        try {
            return $this->decoder->decode(
                $this->cookieManager->getCookie(TrustedManagerInterface::TRUSTED_DEVICE_COOKIE));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Send token as cookie
     * @param string $token
     */
    protected function sendTokenCookie($token)
    {
        $user = $this->getUser();
        $tokenCollection = $this->getTokenCollection();

        // Enable cookie
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDurationOneYear()
            ->setHttpOnly(true)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());

        $tokenCollection[$user->getUserName()] = $token;

        $this->cookieManager->setPublicCookie(
            TrustedManagerInterface::TRUSTED_DEVICE_COOKIE,
            $this->encoder->encode($tokenCollection),
            $cookieMetadata
        );
    }

    /**
     * Rotate secret trust token
     * @return void
     */
    public function rotateTrustedDeviceToken()
    {
        $user = $this->getUser();
        $tokenCollection = $this->getTokenCollection();

        if (isset($tokenCollection[$user->getUserName()])) {
            $token = $tokenCollection[$user->getUserName()];

            /** @var $trustEntry Trusted */
            $trustEntry = $this->trustedFactory->create();
            $this->trustedResourceModel->load($trustEntry, $token, 'token');
            if ($trustEntry->getId() && ($trustEntry->getUserId() == $user->getId())) {
                $token = md5(uniqid(time()));

                $trustEntry->setToken($token);
                $this->trustedResourceModel->save($trustEntry);

                $this->sendTokenCookie($token);
            }
        }
    }

    /**
     * Return true if device is trusted
     * @return bool
     */
    public function isTrustedDevice()
    {
        if (is_null($this->isTrustedDevice)) { // Must cache this ina single session to avoid rotation issues
            $user = $this->getUser();
            $tokenCollection = $this->getTokenCollection();

            if (isset($tokenCollection[$user->getUserName()])) {
                $token = $tokenCollection[$user->getUserName()];

                /** @var $trustEntry Trusted */
                $trustEntry = $this->trustedFactory->create();
                $this->trustedResourceModel->load($trustEntry, $token, 'token');

                $this->isTrustedDevice = $trustEntry->getId() && ($trustEntry->getUserId() == $user->getId());
            } else {
                $this->isTrustedDevice = false;
            }
        }

        return $this->isTrustedDevice;
    }

    /**
     * Revoke trusted device
     * @param int $tokenId
     * @return void
     */
    public function revokeTrustedDevice($tokenId)
    {
        $trustEntry = $this->trustedFactory->create();
        $this->trustedResourceModel->load($trustEntry, $tokenId);
        $this->trustedResourceModel->delete($trustEntry);
    }

    /**
     * Trust a device
     * @param $providerCode
     * @param RequestInterface $request
     */
    public function handleTrustDeviceRequest($providerCode, RequestInterface $request)
    {
        if ($provider = $this->tfa->getProvider($providerCode)) {
            if (
                $provider->getAllowTrustedDevices() &&
                $request->getParam('tfa_trust_device') &&
                ($request->getParam('tfa_trust_device') != "false") // u2fkey submit translates into a string
            ) {
                $token = md5(uniqid(time()));

                /** @var $trustEntry Trusted */
                $trustEntry = $this->trustedFactory->create();
                $trustEntry
                    ->setToken($token)
                    ->setDateTime($this->dateTime->date())
                    ->setUserId($this->getUser()->getId())
                    ->setLastIp($this->remoteAddress->getRemoteAddress())
                    ->setDeviceName($this->getDeviceName())
                    ->setUserAgent($request->getServer('HTTP_USER_AGENT'));

                $this->trustedResourceModel->save($trustEntry);

                $this->sendTokenCookie($token);
            }
        }
    }
}