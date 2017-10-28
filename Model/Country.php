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

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Country extends AbstractModel
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \MSP\TwoFactorAuth\Api\Data\CountryInterfaceFactory
     */
    private $countryDataFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        DataObjectHelper $dataObjectHelper,
        \MSP\TwoFactorAuth\Api\Data\CountryInterfaceFactory $countryDataFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dataObjectHelper = $dataObjectHelper;
        $this->countryDataFactory = $countryDataFactory;
    }

    protected function _construct()
    {
        $this->_init(\MSP\TwoFactorAuth\Model\ResourceModel\Country::class);
    }

    /**
     * Retrieve Country model
     *
     * @return \MSP\TwoFactorAuth\Api\Data\CountryInterface
     */
    public function getDataModel()
    {
        $countryData = $this->getData();

        /** @var \MSP\TwoFactorAuth\Api\Data\CountryInterface $countryDataObject */
        $countryDataObject = $this->countryDataFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $countryDataObject,
            $countryData,
            \MSP\TwoFactorAuth\Api\Data\CountryInterface::class
        );
        $countryDataObject->setId($this->getId());

        return $countryDataObject;
    }
}
