<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class TrustedRegistry
{
    private $registry = [];
    private $registryByKey = [
        'user_id' => [],
    ];

    /**
     * @var \MSP\TwoFactorAuth\Model\TrustedFactory
     */
    private $trustedFactory;

    public function __construct(
        \MSP\TwoFactorAuth\Model\TrustedFactory $trustedFactory
    ) {
        $this->trustedFactory = $trustedFactory;
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
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface|null
     */
    public function retrieveById($id)
    {
        if (isset($this->registry[$id])) {
            return $this->registry[$id];
        }

        return null;
    }

    /**
     * Retrieve by UserId value
     * @param int $value
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface|null
     */
    public function retrieveByUserId($value)
    {
        if (isset($this->registryByKey['user_id'][$value])) {
            return $this->retrieveById($this->registryByKey['user_id'][$value]);
        }

        return null;
    }

    /**
     * Push one object into registry
     * @param \MSP\TwoFactorAuth\Model\Trusted $trusted
     */
    public function push(\MSP\TwoFactorAuth\Model\Trusted $trusted)
    {
        $this->registry[$trusted->getId()] = $trusted->getDataModel();
        foreach (array_keys($this->registryByKey) as $key) {
            $this->registryByKey[$key][$trusted->getData($key)] = $trusted->getId();
        }
    }
}
