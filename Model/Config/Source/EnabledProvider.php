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

use Magento\Framework\Option\ArrayInterface;
use MSP\TwoFactorAuth\Api\TfaInterface;

class EnabledProvider implements ArrayInterface
{
    /**
     * @var TfaInterface
     */
    private $tfa;

    public function __construct(
        TfaInterface $tfa
    ) {
        $this->tfa = $tfa;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $providers = $this->tfa->getAllProviders();
        $res = [];
        foreach ($providers as $provider) {
            if ($provider->isEnabled()) {
                $res[] = [
                    'value' => $provider->getCode(),
                    'label' => $provider->getName(),
                ];
            }
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
        $options = $this->toOptionArray();
        $return = [];

        foreach ($options as $option) {
            $return[$option['value']] = $option['label'];
        }

        return $return;
    }
}
