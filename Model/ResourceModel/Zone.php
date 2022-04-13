<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\ResourceModel;

use Tiargsa\CorreoArgentino\Api\Data\ZoneInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Zone extends AbstractDb
{
    const TABLE = 'Tiargsa_CorreoArgentino_zona';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(self::TABLE, ZoneInterface::ZONE_ID);
    }
}
