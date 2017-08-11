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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\EngineInterface;

class Authy implements EngineInterface
{
    const CODE = 'authy'; // Must be the same as defined in di.xml

    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/authy/enabled';
    const XML_PATH_ALLOW_TRUSTED_DEVICES = 'msp_securitysuite_twofactorauth/authy/allow_trusted_devices';
    const XML_PATH_API_KEY = 'msp_securitysuite_twofactorauth/authy/api_key';

    const AUTHY_BASE_ENDPOINT = 'https://api.authy.com/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DecoderInterface $decoder,
        UserConfigManagerInterface $userConfigManager,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        CurlFactory $curlFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->curlFactory = $curlFactory;
        $this->decoder = $decoder;
        $this->userConfigManager = $userConfigManager;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
    }

    /**
     * Get API key
     * @return string
     */
    protected function getApiKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_API_KEY);
    }

    /**
     * Get authy API endpoint
     * @param string $path
     * @return string
     */
    protected function getProtectedApiEndpoint($path)
    {
        return static::AUTHY_BASE_ENDPOINT . 'protected/json/' . $path;
    }

    /**
     * Get authy API endpoint
     * @param string $path
     * @return string
     */
    protected function getOneTouchApiEndpoint($path)
    {
        return static::AUTHY_BASE_ENDPOINT . 'onetouch/json/' . $path;
    }

    /**
     * Get error from response
     * @param array $response
     * @return string
     */
    protected function getErrorFromResponse($response)
    {
        if ($response === false) {
            return 'Invalid authy webservice response';
        }

        if (!isset($response['success']) || !$response['success']) {
            return $response['message'];
        }

        return false;
    }

    /**
     * Enroll in Authy
     * @param UserInterface $user
     * @return bool
     * @throws LocalizedException
     */
    public function enroll(UserInterface $user)
    {
        $providerInfo = $this->userConfigManager->getProviderConfig($user, Authy::CODE);
        if (!isset($providerInfo['country_code'])) {
            throw new LocalizedException(__('Missing phone information'));
        }

        $url = $this->getProtectedApiEndpoint('users/new');
        $curl = $this->curlFactory->create();

        $curl->addHeader('X-Authy-API-Key', $this->getApiKey());
        $curl->post($url, [
            'user[email]' => $user->getEmail(),
            'user[cellphone]' => $providerInfo['phone_number'],
            'user[country_code]' => $providerInfo['country_code'],
        ]);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        $this->userConfigManager->addProviderConfig($user, Authy::CODE, [
            'user' => $response['user']['id'],
        ]);

        $this->userConfigManager->activateProviderConfiguration($user, Authy::CODE);

        return true;
    }

    /**
     * Verify phone number
     * @param UserInterface $user
     * @param string $country
     * @param string $phoneNumber
     * @param string $method
     * @return true
     * @throws LocalizedException
     */
    public function requestPhoneNumberVerification(UserInterface $user, $country, $phoneNumber, $method)
    {
        $url = $this->getProtectedApiEndpoint('phones/verification/start');

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->getApiKey());
        $curl->post($url, [
            'via' => $method,
            'country_code' => $country,
            'phone_number' => $phoneNumber
        ]);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        $this->userConfigManager->addProviderConfig($user, Authy::CODE, [
            'country_code' => $country,
            'phone_number' => $phoneNumber,
            'carrier' => $response['carrier'],
            'mobile' => $response['is_cellphone'],
            'verify' => [
                'uuid' => $response['uuid'],
                'via' => $method,
                'expires' => $this->dateTime->timestamp() + $response['seconds_to_expire'],
                'seconds_to_expire' => $response['seconds_to_expire'],
                'message' => $response['message'],
            ],
            'phone_confirmed' => false,
        ]);

        return true;
    }

    /**
     * Verify phone number
     * @param UserInterface $user
     * @param string $verificationCode
     * @return true
     * @throws LocalizedException
     */
    public function verifyPhoneNumber(UserInterface $user, $verificationCode)
    {
        $providerInfo = $this->userConfigManager->getProviderConfig($user, Authy::CODE);
        if (!isset($providerInfo['country_code'])) {
            throw new LocalizedException(__('Missing verify request information'));
        }

        $url = $this->getProtectedApiEndpoint('phones/verification/check');

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->getApiKey());
        $curl->get($url . '?' . http_build_query([
            'country_code' => $providerInfo['country_code'],
            'phone_number' => $providerInfo['phone_number'],
            'verification_code' => $verificationCode,
        ]));

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        $this->userConfigManager->addProviderConfig($user, Authy::CODE, [
            'phone_confirmed' => true,
        ]);
        $this->userConfigManager->activateProviderConfiguration($user, Authy::CODE);

        return true;
    }

    /**
     * Request a token
     * @param UserInterface $user
     * @param string $via
     * @return true
     * @throws LocalizedException
     */
    public function requestToken(UserInterface $user, $via)
    {
        if (!in_array($via, ['call', 'sms'])) {
            throw new LocalizedException(__('Unsupported via method'));
        }

        $providerInfo = $this->userConfigManager->getProviderConfig($user, Authy::CODE);
        if (!isset($providerInfo['user'])) {
            throw new LocalizedException(__('Missing user information'));
        }

        $url = $this->getProtectedApiEndpoint('' . $via . '/' . $providerInfo['user']) . '?force=true';

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->getApiKey());
        $curl->get($url);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        return true;
    }

    /**
     * Request one-touch
     * @param UserInterface $user
     * @return true
     * @throws LocalizedException
     */
    public function requestOneTouch(UserInterface $user)
    {
        $providerInfo = $this->userConfigManager->getProviderConfig($user, Authy::CODE);
        if (!isset($providerInfo['user'])) {
            throw new LocalizedException(__('Missing user information'));
        }

        $url = $this->getOneTouchApiEndpoint( 'users/' . $providerInfo['user'] . '/approval_requests');

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->getApiKey());
        $curl->post($url, [
            'message' => ''.__('Login request for %1', $this->storeManager->getStore()->getName()),
            'details[URL]' => $this->storeManager->getStore()->getBaseUrl(),
            'details[User]' => $user->getUserName(),
            'details[Email]' => $user->getEmail(),
            'seconds_to_expire' => 300,
        ]);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        $this->userConfigManager->addProviderConfig($user, Authy::CODE, [
            'pending_approval' => $response['approval_request']['uuid'],
        ]);

        return true;
    }

    /**
     * Verify one-touch
     * @param UserInterface $user
     * @return string
     * @throws LocalizedException
     */
    public function verifyOneTouch(UserInterface $user)
    {
        $providerInfo = $this->userConfigManager->getProviderConfig($user, Authy::CODE);
        if (!isset($providerInfo['user'])) {
            throw new LocalizedException(__('Missing user information'));
        }

        if (!isset($providerInfo['pending_approval'])) {
            throw new LocalizedException(__('No approval requests for this user'));
        }

        $approvalCode = $providerInfo['pending_approval'];

        if (!preg_match('/^\w[\w\-]+\w$/', $approvalCode)) {
            throw new LocalizedException(__('Invalid approval code'));
        }

        $url = $this->getOneTouchApiEndpoint( 'approval_requests/' . $approvalCode);

        $times = 10;

        for ($i=0; $i<$times; $i++) {
            $curl = $this->curlFactory->create();
            $curl->addHeader('X-Authy-API-Key', $this->getApiKey());
            $curl->get($url);

            $response = $this->decoder->decode($curl->getBody());

            if ($errorMessage = $this->getErrorFromResponse($response)) {
                throw new LocalizedException(__($errorMessage));
            }

            $status = $response['approval_request']['status'];
            if ($status == 'pending') {
                // @codingStandardsIgnoreStart
                sleep(1);
                // @codingStandardsIgnoreEnd
                continue;
            }

            if ($status == 'approved') {
                return $status;
            }

            return $status;
        }

        return 'retry';
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function getIsEnabled()
    {
        return
            !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED) &&
            !!$this->getApiKey();
    }

    /**
     * Return true on token validation
     * @param UserInterface $user
     * @param RequestInterface $request
     * @return bool
     * @throws LocalizedException
     */
    public function verify(UserInterface $user, RequestInterface $request)
    {
        $code = $request->getParam('tfa_code');
        if (!preg_match('/^\w+$/', $code)) {
            throw new LocalizedException(__('Invalid code format'));
        }

        $providerInfo = $this->userConfigManager->getProviderConfig($user, Authy::CODE);
        if (!isset($providerInfo['user'])) {
            throw new LocalizedException(__('Missing user information'));
        }

        $url = $this->getProtectedApiEndpoint('verify/' . $code . '/' . $providerInfo['user']);

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->getApiKey());
        $curl->get($url);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        return true;
    }

    /**
     * Return true if this provider allows trusted devices
     * @return boolean
     */
    public function getAllowTrustedDevices()
    {
        return !!$this->scopeConfig->getValue(static::XML_PATH_ALLOW_TRUSTED_DEVICES);
    }
}
