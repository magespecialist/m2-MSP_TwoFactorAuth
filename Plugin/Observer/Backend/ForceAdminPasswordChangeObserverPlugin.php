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

namespace MSP\TwoFactorAuth\Plugin\Observer\Backend;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use MSP\TwoFactorAuth\Api\TfaInterface;

class ForceAdminPasswordChangeObserverPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TfaInterface
     */
    private $tfa;

    public function __construct(
        RequestInterface $request,
        TfaInterface $tfa
    ) {
        $this->request = $request;
        $this->tfa = $tfa;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \Magento\User\Observer\Backend\ForceAdminPasswordChangeObserver $subject
     * @param \Closure $procede
     * @param EventObserver $observer
     * @return void
     */
    public function aroundExecute(
        \Magento\User\Observer\Backend\ForceAdminPasswordChangeObserver $subject,
        \Closure $procede,
        EventObserver $observer
    ) {
        /*
         * We need to bypass ForceAdminPasswordChangeObserver::execute while authenticating 2FA
         * to avoid a recursion loop caused by two different redirects
         */
        $fullActionName = $this->request->getFullActionName();
        if (!in_array($fullActionName, $this->tfa->getAllowedUrls())) {
            $procede($observer);
        }
    }
}
