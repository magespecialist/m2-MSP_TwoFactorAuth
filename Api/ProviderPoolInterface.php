<?php
namespace MSP\TwoFactorAuth\Api;

interface ProviderPoolInterface
{
    /**
     * Get a list of providers
     * @return \MSP\TwoFactorAuth\Model\ProviderInterface[]
     */
    public function getProviders();

    /**
     * Get provider by code
     * @param string $code
     * @return \MSP\TwoFactorAuth\Model\ProviderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProviderByCode($code);
}
