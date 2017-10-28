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

namespace MSP\TwoFactorAuth\Api;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
interface TrustedRepositoryInterface
{
    /**
     * Save object
     * @param \MSP\TwoFactorAuth\Api\Data\TrustedInterface $object
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function save(\MSP\TwoFactorAuth\Api\Data\TrustedInterface $object);

    /**
     * Get object by id
     * @param int $id
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function getById($id);

    /**
     * Get by UserId value
     * @param int $value
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function getByUserId($value);

    /**
     * Delete object
     * @param \MSP\TwoFactorAuth\Api\Data\TrustedInterface $object
     * @return boolean
     */
    public function delete(\MSP\TwoFactorAuth\Api\Data\TrustedInterface $object);

    /**
     * Get a list of object
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
