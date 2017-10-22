<?php
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
 */
class UserConfig extends AbstractModel
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \MSP\TwoFactorAuth\Api\Data\UserConfigInterfaceFactory
     */
    private $userConfigDataFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        DataObjectHelper $dataObjectHelper,
        \MSP\TwoFactorAuth\Api\Data\UserConfigInterfaceFactory $userConfigDataFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dataObjectHelper = $dataObjectHelper;
        $this->userConfigDataFactory = $userConfigDataFactory;
    }

    protected function _construct()
    {
        $this->_init(\MSP\TwoFactorAuth\Model\ResourceModel\UserConfig::class);
    }

    /**
     * Retrieve UserConfig model
     *
     * @return \MSP\TwoFactorAuth\Api\Data\UserConfigInterface
     */
    public function getDataModel()
    {
        $userConfigData = $this->getData();

        /** @var \MSP\TwoFactorAuth\Api\Data\UserConfigInterface $userConfigDataObject */
        $userConfigDataObject = $this->userConfigDataFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $userConfigDataObject,
            $userConfigData,
            \MSP\TwoFactorAuth\Api\Data\UserConfigInterface::class
        );
        $userConfigDataObject->setId($this->getId());

        return $userConfigDataObject;
    }
}
