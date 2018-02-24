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

class AdminUserSaveAfter implements ObserverInterface
{
    /**
     * @var UserConfigManagerInterface
     */
    private $userConfigManager;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        UserConfigManagerInterface $userConfigManager,
        AuthorizationInterface $authorization
    ) {
        $this->userConfigManager = $userConfigManager;
        $this->authorization = $authorization;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->authorization->isAllowed('MSP_TwoFactorAuth::tfa')) {
            $user = $observer->getEvent()->getObject();
            $data = $user->getData();

            if (isset($data['msp_tfa_providers'])) {
                if (!is_array($data['msp_tfa_providers'])) {
                    $data['msp_tfa_providers'] = [];
                }
                $this->userConfigManager->setProvidersCodes($user->getId(), $data['msp_tfa_providers']);
            }
        }
    }
}
