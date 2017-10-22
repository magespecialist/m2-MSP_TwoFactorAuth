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
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Trusted extends AbstractModel
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \MSP\TwoFactorAuth\Api\Data\TrustedInterfaceFactory
     */
    private $trustedDataFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        DataObjectHelper $dataObjectHelper,
        \MSP\TwoFactorAuth\Api\Data\TrustedInterfaceFactory $trustedDataFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dataObjectHelper = $dataObjectHelper;
        $this->trustedDataFactory = $trustedDataFactory;
    }

    protected function _construct()
    {
        $this->_init(\MSP\TwoFactorAuth\Model\ResourceModel\Trusted::class);
    }

    /**
     * Retrieve Trusted model
     *
     * @return \MSP\TwoFactorAuth\Api\Data\TrustedInterface
     */
    public function getDataModel()
    {
        $trustedData = $this->getData();

        /** @var \MSP\TwoFactorAuth\Api\Data\TrustedInterface $trustedDataObject */
        $trustedDataObject = $this->trustedDataFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $trustedDataObject,
            $trustedData,
            \MSP\TwoFactorAuth\Api\Data\TrustedInterface::class
        );
        $trustedDataObject->setId($this->getId());

        return $trustedDataObject;
    }
}
