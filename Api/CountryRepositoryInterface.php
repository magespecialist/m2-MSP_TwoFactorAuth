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
interface CountryRepositoryInterface
{
    /**
     * Save object
     * @param \MSP\TwoFactorAuth\Api\Data\CountryInterface $object
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface
     */
    public function save(\MSP\TwoFactorAuth\Api\Data\CountryInterface $object);

    /**
     * Get object by id
     * @param int $id
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface
     */
    public function getById($id);

    /**
     * Get by Code value
     * @param string $value
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface
     */
    public function getByCode($value);

    /**
     * Delete object
     * @param \MSP\TwoFactorAuth\Api\Data\CountryInterface $object
     * @return boolean
     */
    public function delete(\MSP\TwoFactorAuth\Api\Data\CountryInterface $object);

    /**
     * Get a list of object
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MSP\TwoFactorAuth\Api\Data\CountrySearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
