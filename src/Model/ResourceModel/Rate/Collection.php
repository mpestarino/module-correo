<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\ResourceModel\Rate;

use Tiargsa\CorreoArgentino\Api\Data\RateInterface;
use Tiargsa\CorreoArgentino\Model\Rate;
use Tiargsa\CorreoArgentino\Model\ResourceModel\Rate as ResourceRate;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = RateInterface::RATE_ID;

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(Rate::class, ResourceRate::class);
    }
}
