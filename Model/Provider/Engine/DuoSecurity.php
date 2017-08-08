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

use MSP\TwoFactorAuth\Model\Provider\EngineInterface;

class DuoSecurity implements EngineInterface
{
    const DUO_PREFIX = "TX";
    const APP_PREFIX = "APP";
    const AUTH_PREFIX = "AUTH";

    const DUO_EXPIRE = 300;
    const APP_EXPIRE = 3600;

    const XML_PATH_ENABLED = 'msp_securitysuite_twofactorauth/duo/enabled';
    const XML_PATH_INTEGRATION_KEY = 'msp_securitysuite_twofactorauth/duo/integration_key';
    const XML_PATH_SECRET_KEY = 'msp_securitysuite_twofactorauth/duo/secret_key';
    const XML_PATH_API_HOSTNAME = 'msp_securitysuite_twofactorauth/duo/api_hostname';
    const XML_PATH_APPLICATION_KEY = 'msp_securitysuite_twofactorauth/duo/application_key';
}
