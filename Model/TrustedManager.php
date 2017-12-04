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
use MSP\TwoFactorAuth\Api\TrustedRepositoryInterface;
use MSP\TwoFactorAuth\Model\ResourceModel\Trusted as TrustedResourceModel;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

/**
 * Class TrustedManager
 * @package MSP\TwoFactorAuth\Model
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class TrustedManager implements TrustedManagerInterface
{
    private $isTrustedDevice = null;

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
    /**
     * @var TrustedRepositoryInterface
     */
    private $trustedRepository;

    /**
     * TrustedManager constructor.
     * @param TfaInterface $tfa
     * @param DateTime $dateTime
     * @param Session $session
     * @param RemoteAddress $remoteAddress
     * @param Encoder $encoder
     * @param Decoder $decoder
     * @param TrustedResourceModel $trustedResourceModel
     * @param CookieManagerInterface $cookieManager
     * @param SessionManagerInterface $sessionManager
     * @param TrustedRepositoryInterface $trustedRepository
     * @param TrustedFactory $trustedFactory
     * @param CookieMetadataFactory $cookieMdFactory
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
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
        TrustedRepositoryInterface $trustedRepository,
        TrustedFactory $trustedFactory,
        CookieMetadataFactory $cookieMdFactory
    ) {
        $this->tfa = $tfa;
        $this->trustedFactory = $trustedFactory;
        $this->dateTime = $dateTime;
        $this->remoteAddress = $remoteAddress;
        $this->session = $session;
        $this->trustedResourceModel = $trustedResourceModel;
        $this->cookieManager = $cookieManager;
        $this->sessionManager = $sessionManager;
        $this->cookieMetadataFactory = $cookieMdFactory;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->trustedRepository = $trustedRepository;
    }

    /**
     * Get current user
     * @return User|null
     */
    private function getUser()
    {
        return $this->session->getUser();
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
     * Get token collection from cookie
     * @return array
     */
    private function getTokenCollection()
    {
        try {
            return $this->decoder->decode(
                $this->cookieManager->getCookie(TrustedManagerInterface::TRUSTED_DEVICE_COOKIE)
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Send token as cookie
     * @param string $token
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    private function sendTokenCookie($token)
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
     * @throws \Exception
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
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
                $token = sha1(uniqid(time()));

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
        if ($this->isTrustedDevice === null) { // Must cache this ina single session to avoid rotation issues
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
     * @return bool
     */
    public function revokeTrustedDevice($tokenId)
    {
        $token = $this->trustedRepository->getById($tokenId);
        $this->trustedRepository->delete($token);

        return true;
    }

    /**
     * Trust a device
     * @param $providerCode
     * @param RequestInterface $request
     * @return boolean
     * @throws \Exception
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function handleTrustDeviceRequest($providerCode, RequestInterface $request)
    {
        if ($provider = $this->tfa->getProvider($providerCode)) {
            if ($provider->isTrustedDevicesAllowed() &&
                $request->getParam('tfa_trust_device') &&
                ($request->getParam('tfa_trust_device') != "false") // u2fkey submit translates into a string
            ) {
                $token = sha1(uniqid(time()));

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
                return true;
            }
        }

        return false;
    }
}
