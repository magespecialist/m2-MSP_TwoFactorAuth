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

namespace MSP\TwoFactorAuth\Block;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Registry;
use Magento\User\Model\User;
use MSP\TwoFactorAuth\Api\TfaInterface;

class TrustDevice extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var TfaInterface
     */
    private $tfa;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        Session $session,
        TfaInterface $tfa,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->tfa = $tfa;
        $this->session = $session;
    }

    /**
     * Get current 2FA provider if defined
     * @return string|null
     */
    public function getCurrentProviderCode()
    {
        return $this->registry->registry('msp_tfa_current_provider');
    }

    /**
     * Return true if "trust device" flag should be shown
     * @return boolean
     */
    public function canShowTrustDevice()
    {
        $provider = $this->tfa->getProvider($this->getCurrentProviderCode());
        return $provider->isTrustedDevicesAllowed();
    }
}
