<?php
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
