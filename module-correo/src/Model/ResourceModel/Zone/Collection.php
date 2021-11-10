<?php
/**
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\ResourceModel\Zone;

use Tiargsa\CorreoArgentino\Api\Data\ZoneInterface;
use Tiargsa\CorreoArgentino\Model\Zone;
use Tiargsa\CorreoArgentino\Model\ResourceModel\Zone as ResourceZone;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = ZoneInterface::ZONE_ID;

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(Zone::class, ResourceZone::class);
    }
}
