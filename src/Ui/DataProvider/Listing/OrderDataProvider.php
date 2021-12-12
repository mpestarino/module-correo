<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Ui\DataProvider\Listing;

use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Tiargsa\CorreoArgentino\Model\Carrier\PickupDelivery;
use Tiargsa\CorreoArgentino\Model\Carrier\PriorityDelivery;
use Tiargsa\CorreoArgentino\Model\Carrier\StandardDelivery;

class OrderDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection()
    {
        if (!$this->collection) {
            $collection = $this->collectionFactory->create();
            $select = $collection->getSelect();

            $select->join(
                ["so" => $collection->getTable("sales_order")],
                'main_table.entity_id = so.entity_id',
                ['shipping_method']
            );
            $select->joinLeft(
                ["ss" => $collection->getTable("sales_shipment")],
                'main_table.entity_id = ss.order_id',
                ['order_id']
            );

            $methodsCondition =
                '\'' . PickupDelivery::CARRIER_CODE . '_' . PickupDelivery::METHOD_CODE . '\', ' .
                '\'' . StandardDelivery::CARRIER_CODE . '_' . StandardDelivery::METHOD_CODE . '\', ' .
                '\'' . PriorityDelivery::CARRIER_CODE . '_' . PriorityDelivery::METHOD_CODE . '\'';

            $select->where("ss.order_id IS NULL and so.shipping_method in ($methodsCondition)");

            $this->collection = $collection;
        }
        return $this->collection;
    }
}
