<?php
/**
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\ResourceModel;

use Tiargsa\CorreoArgentino\Api\Data\RateInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Rate extends AbstractDb
{
    const TABLE = 'Tiargsa_CorreoArgentino_tarifa';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(self::TABLE, RateInterface::RATE_ID);
    }
}
