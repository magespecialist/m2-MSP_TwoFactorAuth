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

namespace MSP\TwoFactorAuth\Model\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use MSP\TwoFactorAuth\Api\ProviderInterface;

class DuoSecurity implements ProviderInterface
{
    const XML_PATH_ENABLED = 'msp_securitysuite/twofactorauth_duo/enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get provider name
     * @return string
     */
    public function getName()
    {
        return __('Duo Security');
    }

    /**
     * Return true if this provider can regenerate token
     * @return boolean
     */
    public function canRegenerateToken()
    {
        return false;
    }

    /**
     * Return true if this provider is enabled
     * @return boolean
     */
    public function isEnabled()
    {
        return !!$this->scopeConfig->getValue(static::XML_PATH_ENABLED);
    }

    /**
     * Return a list of reserved actions accessible without 2FA
     * @return array
     */
    public function getExtraAllowedActions()
    {
        return [];
    }
}
