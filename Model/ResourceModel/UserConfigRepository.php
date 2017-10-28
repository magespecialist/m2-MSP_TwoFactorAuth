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

namespace MSP\TwoFactorAuth\Model\ResourceModel;

use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserConfigRepository implements \MSP\TwoFactorAuth\Api\UserConfigRepositoryInterface
{
    /**
     * @var  \MSP\TwoFactorAuth\Api\Data\UserConfigInterfaceFactory
     */
    private $userConfigFactory;

    /**
     * @var \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig
     */
    private $resource;

    /**
     * @var \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \MSP\TwoFactorAuth\Api\Data\UserConfigSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \MSP\TwoFactorAuth\Model\UserConfigRegistry
     */
    private $registry;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    public function __construct(
        \MSP\TwoFactorAuth\Model\UserConfigFactory $userConfigFactory,
        \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig $resource,
        \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig\CollectionFactory $collectionFactory,
        \MSP\TwoFactorAuth\Api\Data\UserConfigSearchResultsInterfaceFactory $searchResultsFactory,
        \MSP\TwoFactorAuth\Model\UserConfigRegistry $registry,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->userConfigFactory = $userConfigFactory;
        $this->resource = $resource;
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\MSP\TwoFactorAuth\Api\Data\UserConfigInterface $userConfig)
    {
        $userConfigData = $this->extensibleDataObjectConverter->toNestedArray(
            $userConfig,
            [],
            \MSP\TwoFactorAuth\Api\Data\UserConfigInterface::class
        );

        /** @var \MSP\TwoFactorAuth\Model\UserConfig $userConfigModel */
        $userConfigModel = $this->userConfigFactory->create(['data' => $userConfigData]);
        $userConfigModel->setDataChanges(true);
        $this->resource->save($userConfigModel);
        $userConfig->setId($userConfigModel->getId());

        $this->registry->push($userConfigModel);

        return $this->getById($userConfigModel->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $fromRegistry = $this->registry->retrieveById($id);
        if ($fromRegistry === null) {
            $userConfig = $this->userConfigFactory->create();
            $this->resource->load($userConfig, $id);

            if (!$userConfig->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('No such UserConfig'));
            }

            $this->registry->push($userConfig);
        }

        return $this->registry->retrieveById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getByUserId($value)
    {
        $fromRegistry = $this->registry->retrieveByUserId($value);
        if ($fromRegistry === null) {
            $userConfig = $this->userConfigFactory->create();
            $this->resource->load($userConfig, $value, 'user_id');

            if (!$userConfig->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('No such UserConfig'));
            }

            $this->registry->push($userConfig);
        }

        return $this->registry->retrieveByUserId($value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\MSP\TwoFactorAuth\Api\Data\UserConfigInterface $userConfig)
    {
        $userConfigData = $this->extensibleDataObjectConverter->toNestedArray(
            $userConfig,
            [],
            \MSP\TwoFactorAuth\Api\Data\UserConfigInterface::class
        );

        /** @var \MSP\TwoFactorAuth\Model\UserConfig $userConfigModel */
        $userConfigModel = $this->userConfigFactory->create(['data' => $userConfigData]);
        $userConfigModel->setData($this->resource->getIdFieldName(), $userConfig->getId());

        $this->resource->delete($userConfigModel);
        $this->registry->removeById($userConfigModel->getId());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \MSP\TwoFactorAuth\Api\Data\UserConfigSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig\Collection $collection */
        $collection = $this->userConfigFactory->create()->getCollection();
        $this->applySearchCriteriaToCollection($searchCriteria, $collection);

        $items = $this->convertCollectionToDataItemsArray($collection);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($items);

        return $searchResults;
    }

    private function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig\Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $fields[] = $filter->getField();

            $conditions[] = [$condition => $filter->getValue()];
        }

        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    private function convertCollectionToDataItemsArray(
        \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig\Collection $collection
    ) {
        $vendors = array_map(function (\MSP\TwoFactorAuth\Model\UserConfig $item) {
            return $item->getDataModel();
        }, $collection->getItems());

        return $vendors;
    }

    private function applySearchCriteriaToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig\Collection $collection
    ) {
        $this->applySearchCriteriaFiltersToCollection($searchCriteria, $collection);
        $this->applySearchCriteriaSortOrdersToCollection($searchCriteria, $collection);
        $this->applySearchCriteriaPagingToCollection($searchCriteria, $collection);
    }

    private function applySearchCriteriaFiltersToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig\Collection $collection
    ) {
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
    }

    private function applySearchCriteriaSortOrdersToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig\Collection $collection
    ) {
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $isAscending = $sortOrder->getDirection() == \Magento\Framework\Api\SortOrder::SORT_ASC;
                $collection->addOrder($sortOrder->getField(), $isAscending ? 'ASC' : 'DESC');
            }
        }
    }

    private function applySearchCriteriaPagingToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\UserConfig\Collection $collection
    ) {
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
    }
}
