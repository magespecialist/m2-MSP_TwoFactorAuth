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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy;

class Verification
{
    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * Verification constructor.
     * @param CurlFactory $curlFactory
     * @param DecoderInterface $decoder
     * @param DateTime $dateTime
     * @param UserConfigManagerInterface $userConfigManager
     * @param Service $service
     */
    public function __construct(
        CurlFactory $curlFactory,
        DecoderInterface $decoder,
        DateTime $dateTime,
        UserConfigManagerInterface $userConfigManager,
        Service $service
    ) {
        $this->curlFactory = $curlFactory;
        $this->service = $service;
        $this->userConfigManager = $userConfigManager;
        $this->decoder = $decoder;
        $this->dateTime = $dateTime;
    }

    /**
     * Verify phone number
     * @param UserInterface $user
     * @param string $country
     * @param string $phoneNumber
     * @param string $method
     * @param array &$response
     * @return true
     * @throws LocalizedException
     */
    public function request(UserInterface $user, $country, $phoneNumber, $method, &$response)
    {
        $url = $this->service->getProtectedApiEndpoint('phones/verification/start');

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->service->getApiKey());
        $curl->post($url, [
            'via' => $method,
            'country_code' => $country,
            'phone_number' => $phoneNumber
        ]);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->service->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        $this->userConfigManager->addProviderConfig($user->getId(), Authy::CODE, [
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
    public function verify(UserInterface $user, $verificationCode)
    {
        $providerInfo = $this->userConfigManager->getProviderConfig($user->getId(), Authy::CODE);
        if (!isset($providerInfo['country_code'])) {
            throw new LocalizedException(__('Missing verify request information'));
        }

        $url = $this->service->getProtectedApiEndpoint('phones/verification/check');

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->service->getApiKey());
        $curl->get($url . '?' . http_build_query([
                'country_code' => $providerInfo['country_code'],
                'phone_number' => $providerInfo['phone_number'],
                'verification_code' => $verificationCode,
            ]));

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->service->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        $this->userConfigManager->addProviderConfig($user->getId(), Authy::CODE, [
            'phone_confirmed' => true,
        ]);
        $this->userConfigManager->activateProviderConfiguration($user->getId(), Authy::CODE);

        return true;
    }
}
