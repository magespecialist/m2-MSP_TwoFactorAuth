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
class TrustedRepository implements \MSP\TwoFactorAuth\Api\TrustedRepositoryInterface
{
    /**
     * @var  \MSP\TwoFactorAuth\Api\Data\TrustedInterfaceFactory
     */
    private $trustedFactory;

    /**
     * @var \MSP\TwoFactorAuth\Model\ResourceModel\Trusted
     */
    private $resource;

    /**
     * @var \MSP\TwoFactorAuth\Model\ResourceModel\Trusted\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \MSP\TwoFactorAuth\Api\Data\TrustedSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var \MSP\TwoFactorAuth\Model\TrustedRegistry
     */
    private $registry;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    public function __construct(
        \MSP\TwoFactorAuth\Model\TrustedFactory $trustedFactory,
        \MSP\TwoFactorAuth\Model\ResourceModel\Trusted $resource,
        \MSP\TwoFactorAuth\Model\ResourceModel\Trusted\CollectionFactory $collectionFactory,
        \MSP\TwoFactorAuth\Api\Data\TrustedSearchResultsInterfaceFactory $searchResultsFactory,
        \MSP\TwoFactorAuth\Model\TrustedRegistry $registry,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->trustedFactory = $trustedFactory;
        $this->resource = $resource;
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\MSP\TwoFactorAuth\Api\Data\TrustedInterface $trusted)
    {
        $trustedData = $this->extensibleDataObjectConverter->toNestedArray(
            $trusted,
            [],
            \MSP\TwoFactorAuth\Api\Data\TrustedInterface::class
        );

        /** @var \MSP\TwoFactorAuth\Model\Trusted $trustedModel */
        $trustedModel = $this->trustedFactory->create(['data' => $trustedData]);
        $trustedModel->setDataChanges(true);
        $this->resource->save($trustedModel);
        $trusted->setId($trustedModel->getId());

        $this->registry->push($trustedModel);

        return $this->getById($trustedModel->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $fromRegistry = $this->registry->retrieveById($id);
        if ($fromRegistry === null) {
            $trusted = $this->trustedFactory->create();
            $this->resource->load($trusted, $id);

            if (!$trusted->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('No such Trusted'));
            }

            $this->registry->push($trusted);
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
            $trusted = $this->trustedFactory->create();
            $this->resource->load($trusted, $value, 'user_id');

            if (!$trusted->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('No such Trusted'));
            }

            $this->registry->push($trusted);
        }

        return $this->registry->retrieveByUserId($value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\MSP\TwoFactorAuth\Api\Data\TrustedInterface $trusted)
    {
        $trustedData = $this->extensibleDataObjectConverter->toNestedArray(
            $trusted,
            [],
            \MSP\TwoFactorAuth\Api\Data\TrustedInterface::class
        );

        /** @var \MSP\TwoFactorAuth\Model\Trusted $trustedModel */
        $trustedModel = $this->trustedFactory->create(['data' => $trustedData]);
        $trustedModel->setData($this->resource->getIdFieldName(), $trusted->getId());

        $this->resource->delete($trustedModel);
        $this->registry->removeById($trustedModel->getId());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \MSP\TwoFactorAuth\Api\Data\TrustedSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \MSP\TwoFactorAuth\Model\ResourceModel\Trusted\Collection $collection */
        $collection = $this->trustedFactory->create()->getCollection();
        $this->applySearchCriteriaToCollection($searchCriteria, $collection);

        $items = $this->convertCollectionToDataItemsArray($collection);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($items);

        return $searchResults;
    }

    private function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \MSP\TwoFactorAuth\Model\ResourceModel\Trusted\Collection $collection
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
        \MSP\TwoFactorAuth\Model\ResourceModel\Trusted\Collection $collection
    ) {
        $vendors = array_map(function (\MSP\TwoFactorAuth\Model\Trusted $item) {
            return $item->getDataModel();
        }, $collection->getItems());

        return $vendors;
    }

    private function applySearchCriteriaToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\Trusted\Collection $collection
    ) {
        $this->applySearchCriteriaFiltersToCollection($searchCriteria, $collection);
        $this->applySearchCriteriaSortOrdersToCollection($searchCriteria, $collection);
        $this->applySearchCriteriaPagingToCollection($searchCriteria, $collection);
    }

    private function applySearchCriteriaFiltersToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\Trusted\Collection $collection
    ) {
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
    }

    private function applySearchCriteriaSortOrdersToCollection(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \MSP\TwoFactorAuth\Model\ResourceModel\Trusted\Collection $collection
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
        \MSP\TwoFactorAuth\Model\ResourceModel\Trusted\Collection $collection
    ) {
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
    }
}
