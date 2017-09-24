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

namespace MSP\TwoFactorAuth\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;
use MSP\TwoFactorAuth\Api\TrustedManagerInterface;

class BackendAuthUserLoginSuccess implements ObserverInterface
{
    /**
     * @var TrustedManagerInterface
     */
    private $trustedManager;

    /**
     * @var TfaInterface
     */
    private $tfa;

    public function __construct(
        TfaInterface $tfa,
        TrustedManagerInterface $trustedManager
    ) {
        $this->trustedManager = $trustedManager;
        $this->tfa = $tfa;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function execute(Observer $observer)
    {
        if (!$this->tfa->isEnabled()) {
            return;
        }

        if ($this->trustedManager->isTrustedDevice()) {
            $this->trustedManager->rotateTrustedDeviceToken();
        }
    }
}
