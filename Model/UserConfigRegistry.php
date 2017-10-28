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
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class UserConfigRegistry
{
    private $registry = [];
    private $registryByKey = [
        'user_id' => [],
    ];

    /**
     * @var \MSP\TwoFactorAuth\Model\UserConfigFactory
     */
    private $userConfigFactory;

    public function __construct(
        \MSP\TwoFactorAuth\Model\UserConfigFactory $userConfigFactory
    ) {
        $this->userConfigFactory = $userConfigFactory;
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
     * @return \MSP\TwoFactorAuth\Api\Data\UserConfigInterface|null
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
     * @return \MSP\TwoFactorAuth\Api\Data\UserConfigInterface|null
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
     * @param \MSP\TwoFactorAuth\Model\UserConfig $userConfig
     */
    public function push(\MSP\TwoFactorAuth\Model\UserConfig $userConfig)
    {
        $this->registry[$userConfig->getId()] = $userConfig->getDataModel();
        foreach (array_keys($this->registryByKey) as $key) {
            $this->registryByKey[$key][$userConfig->getData($key)] = $userConfig->getId();
        }
    }
}
