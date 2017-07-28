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

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\User\Model\User;
use MSP\TwoFactorAuth\Api\ProviderConfigInterface;

class ProviderConfig implements ProviderConfigInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(
        EncoderInterface $encoder,
        DecoderInterface $decoder
    ) {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    /**
     * Save user TFA config
     * @param array $config
     * @param string $providerCode
     * @param User $user
     * @return array
     */
    public function setUserProviderConfiguration($config, $providerCode, User $user)
    {
        try {
            $currentConfig = $this->decoder->decode($user->getMspTfaConfig());
        } catch (\Exception $e) {
            $currentConfig = [];
        }

        if (!isset($currentConfig[$providerCode])) {
            $currentConfig[$providerCode] = [];
        }

        $currentConfig[$providerCode] = $config;
        $user
            ->setMspTfaConfig($this->encoder->encode($currentConfig))
            ->save();

        return $config;
    }

    /**
     * Get user TFA config
     * @param string $providerCode
     * @param User $user
     * @return array
     */
    public function getUserProviderConfiguration($providerCode, User $user)
    {
        try {
            $currentConfig = $this->decoder->decode($user->getMspTfaConfig());
        } catch (\Exception $e) {
            return [];
        }

        if (!isset($currentConfig[$providerCode])) {
            return [];
        }

        return $currentConfig[$providerCode];
    }
}
