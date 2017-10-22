<?php
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
