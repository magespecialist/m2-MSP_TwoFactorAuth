<?php
/**
 * Automatically created by MageSpecialist CodeMonkey
 * https://github.com/magespecialist/m2-MSP_CodeMonkey
 */

namespace MSP\TwoFactorAuth\Model\ResourceModel;

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class UserConfig extends AbstractDb
{
    /**
     * @var DecoderInterface
     */
    private $decoder;
    /**
     * @var EncoderInterface
     */
    private $encoder;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        DecoderInterface $decoder,
        EncoderInterface $encoder,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->decoder = $decoder;
        $this->encoder = $encoder;
    }

    protected function _construct()
    {
        $this->_init('msp_tfa_user_config', 'msp_tfa_user_config_id');
    }

    public function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterLoad($object);

        try {
            $object->setData('config', $this->decoder->decode($object->getData('encoded_config')));
        } catch (\Exception $e) {
            $object->setData('config', []);
        }

        try {
            $object->setData('providers', $this->decoder->decode($object->getData('encoded_providers')));
        } catch (\Exception $e) {
            $object->setData('providers', []);
        }

        return $this;
    }

    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setData('encoded_config', $this->encoder->encode($object->getData('config')));
        $object->setData('encoded_providers', $this->encoder->encode($object->getData('providers')));

        parent::_beforeSave($object);
    }
}
