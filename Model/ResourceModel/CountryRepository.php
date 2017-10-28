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
class CountryRepository implements \MSP\TwoFactorAuth\Api\CountryRepositoryInterface
{
    /**
     * @var  \MSP\TwoFactorAuth\Api\Data\CountryInterfaceFactory
     */
    private $countryFactory;

    /**
     * @var \MSP\TwoFactorAuth\Model\ResourceModel\Country
     */
    private $resource;

    /**
     * @var \MSP\TwoFactorAuth\Model\ResourceModel\Country\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \MSP\TwoFactorAuth\Api\Data\CountrySearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \MSP\TwoFactorAuth\Model\CountryRegistry
     */
    private $registry;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    public function __construct(
        \MSP\TwoFactorAuth\Model\CountryFactory $countryFactory,
        \MSP\TwoFactorAuth\Model\ResourceModel\Country $resource,
        \MSP\TwoFactorAuth\Model\ResourceModel\Country\CollectionFactory $collectionFactory,
        \MSP\TwoFactorAuth\Api\Data\CountrySearchResultsInterfaceFactory $searchResultsFactory,
        \MSP\TwoFactorAuth\Model\CountryRegistry $registry,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->countryFactory = $countryFactory;
        $this->resource = $resource;
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\MSP\TwoFactorAuth\Api\Data\CountryInterface $country)
    {
        $countryData = $this->extensibleDataObjectConverter->toNestedArray(
            $country,
            [],
            \MSP\TwoFactorAuth\Api\Data\CountryInterface::class
        );

        /** @var \MSP\TwoFactorAuth\Model\Country $countryModel */
        $countryModel = $this->countryFactory->create(['data' => $countryData]);
        $countryModel->setDataChanges(true);
        $this->resource->save($countryModel);
        $country->setId($countryModel->getId());

        $this->registry->push($countryModel);

        return $this->getById($countryModel->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $fromRegistry = $this->registry->retrieveById($id);
        if ($fromRegistry === null) {
            $country = $this->countryFactory->create();
            $this->resource->load($country, $id);

            if (!$country->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('No such Country'));
            }

            $this->registry->push($country);
        }

        return $this->registry->retrieveById($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getByCode($value)
    {
        $fromRegistry = $this->registry->retrieveByCode($value);
        if ($fromRegistry === null) {
            $country = $this->countryFactory->create();
            $this->resource->load($country, $value, 'code');

            if (!$country->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('No such Country'));
            }

            $this->registry->push($country);
        }

        return $this->registry->retrieveByCode($value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\MSP\TwoFactorAuth\Api\Data\CountryInterface $country)
    {
        $countryData = $this->extensibleDataObjectConverter->toNestedArray(
            $country,
            [],
            \MSP\TwoFactorAuth\Api\Data\CountryInterface::class
        );

        /** @var \MSP\TwoFactorAuth\Model\Country $countryModel */
        $countryModel = $this->countryFactory->create(['data' => $countryData]);
        $countryModel->setData($this->resource->getIdFieldName(), $country->getId());

        $this->resource->delete($countryModel);
        $this->registry->removeById($countryModel->getId());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \MSP\TwoFactorAuth\Api\Data\CountrySearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \MSP\TwoFactorAuth\Model\ResourceModel\Country\Collection $collection */
        $collection = $this->countryFactory->create()->getCollection();
        $this->applySearchCriteriaToCollection($searchCriteria, $collection);

        $items = $this->convertCollectionToDataItemsArray($collection);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($items);

        return $searchResults;
    }

    private function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \MSP\TwoFactorAuth\Model\ResourceModel\Country\Collection $collection
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
        \MSP\TwoFactorAuth\Model\ResourceModel\Country\Collection $collection
    ) {
        $vendors = array_map(function (\MSP\TwoFactorAuth\Model\Country $item) {
            return $item->getDataModel();
        }, $collection->getItems());

        return $vendors;
    }

    private function applySearchCriteriaToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\Country\Collection $collection
    ) {
        $this->applySearchCriteriaFiltersToCollection($searchCriteria, $collection);
        $this->applySearchCriteriaSortOrdersToCollection($searchCriteria, $collection);
        $this->applySearchCriteriaPagingToCollection($searchCriteria, $collection);
    }

    private function applySearchCriteriaFiltersToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\Country\Collection $collection
    ) {
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
    }

    private function applySearchCriteriaSortOrdersToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\Country\Collection $collection
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
        \MSP\TwoFactorAuth\Model\ResourceModel\Country\Collection $collection
    ) {
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
    }
}
