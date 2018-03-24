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

namespace MSP\TwoFactorAuth\Model\Provider\Engine\Authy;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Json\DecoderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy;

class OneTouch
{
    const XML_PATH_ONETOUCH_MESSAGE = 'msp_securitysuite_twofactorauth/authy/onetouch_message';

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * OneTouch constructor.
     * @param CurlFactory $curlFactory
     * @param UserConfigManagerInterface $userConfigManager
     * @param DecoderInterface $decoder
     * @param Service $service
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CurlFactory $curlFactory,
        UserConfigManagerInterface $userConfigManager,
        DecoderInterface $decoder,
        Service $service,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->curlFactory = $curlFactory;
        $this->userConfigManager = $userConfigManager;
        $this->storeManager = $storeManager;
        $this->service = $service;
        $this->decoder = $decoder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Request one-touch
     * @param UserInterface $user
     * @return true
     * @throws LocalizedException
     */
    public function request(UserInterface $user)
    {
        $providerInfo = $this->userConfigManager->getProviderConfig($user->getId(), Authy::CODE);
        if (!isset($providerInfo['user'])) {
            throw new LocalizedException(__('Missing user information'));
        }

        $url = $this->service->getOneTouchApiEndpoint('users/' . $providerInfo['user'] . '/approval_requests');

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->service->getApiKey());
        $curl->post($url, [
            'message' => $this->scopeConfig->getValue(self::XML_PATH_ONETOUCH_MESSAGE),
            'details[URL]' => $this->storeManager->getStore()->getBaseUrl(),
            'details[User]' => $user->getUserName(),
            'details[Email]' => $user->getEmail(),
            'seconds_to_expire' => 300,
        ]);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->service->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        $this->userConfigManager->addProviderConfig($user->getId(), Authy::CODE, [
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
    public function verify(UserInterface $user)
    {
        $providerInfo = $this->userConfigManager->getProviderConfig($user->getId(), Authy::CODE);
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

        $url = $this->service->getOneTouchApiEndpoint('approval_requests/' . $approvalCode);

        $times = 10;

        for ($i=0; $i<$times; $i++) {
            $curl = $this->curlFactory->create();
            $curl->addHeader('X-Authy-API-Key', $this->service->getApiKey());
            $curl->get($url);

            $response = $this->decoder->decode($curl->getBody());

            if ($errorMessage = $this->service->getErrorFromResponse($response)) {
                throw new LocalizedException(__($errorMessage));
            }

            $status = $response['approval_request']['status'];
            if ($status == 'pending') {
                // @codingStandardsIgnoreStart
                sleep(1); // I know... but it is the only option I have here
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
}
