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

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MSP\TwoFactorAuth\Api\UserConfigManagerInterface;

class AdminUserLoadAfter implements ObserverInterface
{
    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    public function __construct(
        UserConfigManagerInterface $userConfigManager
    ) {
        $this->userConfigManager = $userConfigManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $user = $observer->getEvent()->getObject();
        $user->setData('msp_tfa_providers', $this->userConfigManager->getProvidersCodes($user->getId()));
    }
}
