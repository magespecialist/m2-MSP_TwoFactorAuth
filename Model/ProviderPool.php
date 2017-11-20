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

namespace MSP\TwoFactorAuth\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use MSP\TwoFactorAuth\Api\ProviderPoolInterface;

class ProviderPool implements ProviderPoolInterface
{
    /**
     * @var \MSP\TwoFactorAuth\Api\ProviderInterface[]
     */
    private $providers = [];

    public function __construct(
        $providers = []
    ) {
        $this->providers = $providers;
    }

    /**
     * Get a list of providers
     * @return \MSP\TwoFactorAuth\Api\ProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Get provider by code
     * @param string $code
     * @return \MSP\TwoFactorAuth\Api\ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderByCode($code)
    {
        if ($code) {
            $providers = $this->getProviders();
            if (isset($providers[$code])) {
                return $providers[$code];
            }
        }

        throw new NoSuchEntityException(__('Unknown provider %1', $code));
    }
}
