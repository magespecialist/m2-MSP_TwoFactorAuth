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

class Service
{
    const XML_PATH_API_KEY = 'msp_securitysuite_twofactorauth/authy/api_key';
    const AUTHY_BASE_ENDPOINT = 'https://api.authy.com/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Service constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get API key
     * @return string
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_API_KEY);
    }

    /**
     * Get authy API endpoint
     * @param string $path
     * @return string
     */
    public function getProtectedApiEndpoint($path)
    {
        return static::AUTHY_BASE_ENDPOINT . 'protected/json/' . $path;
    }

    /**
     * Get authy API endpoint
     * @param string $path
     * @return string
     */
    public function getOneTouchApiEndpoint($path)
    {
        return static::AUTHY_BASE_ENDPOINT . 'onetouch/json/' . $path;
    }

    /**
     * Get error from response
     * @param array $response
     * @return string
     */
    public function getErrorFromResponse($response)
    {
        if ($response === false) {
            return 'Invalid authy webservice response';
        }

        if (!isset($response['success']) || !$response['success']) {
            return $response['message'];
        }

        return false;
    }
}
