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

namespace MSP\TwoFactorAuth\Model\Config\Source;

use MSP\TwoFactorAuth\Api\ProviderManagementInterface;

class Provider implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ProviderManagementInterface
     */
    private $providerManagement;

    public function __construct(
        ProviderManagementInterface $providerManagement
    ) {
        $this->providerManagement = $providerManagement;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];

        $providers = $this->toArray();

        foreach ($providers as $code => $label) {
            $res[] = [
                'value' => $code,
                'label' => $label,
            ];
        }

        return $res;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $res = [ProviderManagementInterface::PROVIDER_NONE => __('Disabled')];

        $providers = $this->providerManagement->getAllProviders();
        foreach ($providers as $code => $provider) {
            if ($provider->isEnabled()) {
                $res[$code] = $provider->getName();
            }
        }

        return $res;
    }
}