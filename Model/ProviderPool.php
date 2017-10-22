<?php
namespace MSP\TwoFactorAuth\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use MSP\TwoFactorAuth\Api\ProviderPoolInterface;

class ProviderPool implements ProviderPoolInterface
{
    /**
     * @var \MSP\TwoFactorAuth\Model\ProviderInterface[]
     */
    private $providers = [];

    public function __construct(
        $providers = []
    ) {
        $this->providers = $providers;
    }

    /**
     * Get a list of providers
     * @return \MSP\TwoFactorAuth\Model\ProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Get provider by code
     * @param string $code
     * @return \MSP\TwoFactorAuth\Model\ProviderInterface
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
