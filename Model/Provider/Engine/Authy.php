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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Json\DecoderInterface;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Api\EngineInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy\Service;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy\Token;

class Authy implements EngineInterface
{
    const CODE = 'authy'; // Must be the same as defined in di.xml
    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/authy/enabled';
    const XML_PATH_ALLOW_TRUSTED_DEVICES = 'msp_securitysuite_twofactorauth/authy/allow_trusted_devices';

    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

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
     * @var Token
     */
    private $token;

    /**
     * Authy constructor.
     * @param UserConfigManagerInterface $userConfigManager
     * @param DecoderInterface $decoder
     * @param ScopeConfigInterface $scopeConfig
     * @param Token $token
     * @param Service $service
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        UserConfigManagerInterface $userConfigManager,
        DecoderInterface $decoder,
        ScopeConfigInterface $scopeConfig,
        Token $token,
        Service $service,
        CurlFactory $curlFactory
    ) {
        $this->userConfigManager = $userConfigManager;
        $this->curlFactory = $curlFactory;
        $this->service = $service;
        $this->decoder = $decoder;
        $this->scopeConfig = $scopeConfig;
        $this->token = $token;
    }

    /**
     * Enroll in Authy
     * @param UserInterface $user
     * @return bool
     * @throws LocalizedException
     */
    public function enroll(UserInterface $user)
    {
        $providerInfo = $this->userConfigManager->getProviderConfig($user->getId(), Authy::CODE);
        if (!isset($providerInfo['country_code'])) {
            throw new LocalizedException(__('Missing phone information'));
        }

        $url = $this->service->getProtectedApiEndpoint('users/new');
        $curl = $this->curlFactory->create();

        $curl->addHeader('X-Authy-API-Key', $this->service->getApiKey());
        $curl->post($url, [
            'user[email]' => $user->getEmail(),
            'user[cellphone]' => $providerInfo['phone_number'],
            'user[country_code]' => $providerInfo['country_code'],
        ]);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->service->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        $this->userConfigManager->addProviderConfig($user->getId(), Authy::CODE, [
            'user' => $response['user']['id'],
        ]);

        $this->userConfigManager->activateProviderConfiguration($user->getId(), Authy::CODE);

        return true;
    }

    /**
     * Return true if this provider has been enabled by admin
     * @return boolean
     */
    public function isEnabled()
    {
        return
            !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED) &&
            !!$this->service->getApiKey();
    }

    /**
     * Return true on token validation
     * @param UserInterface $user
     * @param DataObject $request
     * @return bool
     * @throws LocalizedException
     */
    public function verify(UserInterface $user, DataObject $request)
    {
        return $this->token->verify($user, $request);
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
