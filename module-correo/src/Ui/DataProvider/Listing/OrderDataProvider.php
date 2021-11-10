<?php
/**
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Ui\DataProvider\Listing;

use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory;

class OrderDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
    }

    public function getCollection()
    {
        if(!$this->collection){
            $collection = $this->collectionFactory->create();
            $select = $collection->getSelect();

            $select->join(
                ["so" => $collection->getTable("sales_order")],
                'main_table.entity_id = so.entity_id',
                array('shipping_method')
            );
            $select->joinLeft(
                ["ss" => $collection->getTable("sales_shipment")],
                'main_table.entity_id = ss.order_id',
                array('order_id')
            );

            $methodsCondition =
                '\'' . \Tiargsa\CorreoArgentino\Model\Carrier\PickupDelivery::CARRIER_CODE . '_' . \Tiargsa\CorreoArgentino\Model\Carrier\PickupDelivery::METHOD_CODE . '\', ' .
                '\'' . \Tiargsa\CorreoArgentino\Model\Carrier\StandardDelivery::CARRIER_CODE . '_' . \Tiargsa\CorreoArgentino\Model\Carrier\StandardDelivery::METHOD_CODE . '\', ' .
                '\'' . \Tiargsa\CorreoArgentino\Model\Carrier\PriorityDelivery::CARRIER_CODE . '_' . \Tiargsa\CorreoArgentino\Model\Carrier\PriorityDelivery::METHOD_CODE . '\'';

            $select->where("ss.order_id IS NULL and so.shipping_method in ($methodsCondition)");

            $this->collection = $collection;
        }
        return $this->collection;
    }
}