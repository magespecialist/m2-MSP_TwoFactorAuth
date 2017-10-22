<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Api\Data;

interface UserConfigSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get an array of objects
     * @return \MSP\TwoFactorAuth\Api\Data\UserConfigInterface[]
     */
    public function getItems();

    /**
     * Set objects list
     * @param \MSP\TwoFactorAuth\Api\Data\UserConfigInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
