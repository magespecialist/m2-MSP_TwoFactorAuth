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

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Json\DecoderInterface;
use Magento\User\Api\Data\UserInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;
use MSP\TwoFactorAuth\Model\Provider\Engine\Authy;

class Token
{
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
     * Token constructor.
     * @param UserConfigManagerInterface $userConfigManager
     * @param Service $service
     * @param DecoderInterface $decoder
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        UserConfigManagerInterface $userConfigManager,
        Service $service,
        DecoderInterface $decoder,
        CurlFactory $curlFactory
    ) {
        $this->userConfigManager = $userConfigManager;
        $this->curlFactory = $curlFactory;
        $this->service = $service;
        $this->decoder = $decoder;
    }

    /**
     * Request a token
     * @param UserInterface $user
     * @param string $via
     * @return true
     * @throws LocalizedException
     */
    public function request(UserInterface $user, $via)
    {
        if (!in_array($via, ['call', 'sms'])) {
            throw new LocalizedException(__('Unsupported via method'));
        }

        $providerInfo = $this->userConfigManager->getProviderConfig($user->getId(), Authy::CODE);
        if (!isset($providerInfo['user'])) {
            throw new LocalizedException(__('Missing user information'));
        }

        $url = $this->service->getProtectedApiEndpoint('' . $via . '/' . $providerInfo['user']) . '?force=true';

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->service->getApiKey());
        $curl->get($url);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->service->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        return true;
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
        $code = $request->getData('tfa_code');
        if (!preg_match('/^\w+$/', $code)) {
            throw new LocalizedException(__('Invalid code format'));
        }

        $providerInfo = $this->userConfigManager->getProviderConfig($user->getId(), Authy::CODE);
        if (!isset($providerInfo['user'])) {
            throw new LocalizedException(__('Missing user information'));
        }

        $url = $this->service->getProtectedApiEndpoint('verify/' . $code . '/' . $providerInfo['user']);

        $curl = $this->curlFactory->create();
        $curl->addHeader('X-Authy-API-Key', $this->service->getApiKey());
        $curl->get($url);

        $response = $this->decoder->decode($curl->getBody());

        if ($errorMessage = $this->service->getErrorFromResponse($response)) {
            throw new LocalizedException(__($errorMessage));
        }

        return true;
    }
}
