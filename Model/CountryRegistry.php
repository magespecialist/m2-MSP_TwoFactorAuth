<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class CountryRegistry
{
    private $registry = [];
    private $registryByKey = [
        'code' => [],
    ];

    /**
     * @var \MSP\TwoFactorAuth\Model\CountryFactory
     */
    private $countryFactory;

    public function __construct(
        \MSP\TwoFactorAuth\Model\CountryFactory $countryFactory
    ) {
        $this->countryFactory = $countryFactory;
    }
    
    /**
     * Remove registry entity by id
     * @param int $id
     */
    public function removeById($id)
    {
        if (isset($this->registry[$id])) {
            unset($this->registry[$id]);
        }

        foreach (array_keys($this->registryByKey) as $key) {
            $reverseMap = array_flip($this->registryByKey[$key]);
            if (isset($reverseMap[$id])) {
                unset($this->registryByKey[$key][$reverseMap[$id]]);
            }
        }
    }

    /**
     * Push one object into registry
     * @param int $id
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface|null
     */
    public function retrieveById($id)
    {
        if (isset($this->registry[$id])) {
            return $this->registry[$id];
        }

        return null;
    }

    /**
     * Retrieve by Code value
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface|null
     */
    public function retrieveByCode($value)
    {
        if (isset($this->registryByKey['code'][$value])) {
            return $this->retrieveById($this->registryByKey['code'][$value]);
        }

        return null;
    }

    /**
     * Push one object into registry
     * @param \MSP\TwoFactorAuth\Model\Country $country
     */
    public function push(\MSP\TwoFactorAuth\Model\Country $country)
    {
        $this->registry[$country->getId()] = $country->getDataModel();
        foreach (array_keys($this->registryByKey) as $key) {
            $this->registryByKey[$key][$country->getData($key)] = $country->getId();
        }
    }
}
