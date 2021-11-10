<?php

namespace Lyracons\OrderReports\Controller\Adminhtml\ExportController;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class ExportData extends Action
{
    protected $orderCollectionFactory;

    protected $orderRepository;

    protected $order;

    protected $itemRepository;

    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        Order $order
    ) {
        $this->order = $order;
        $this->orderRepository = $orderRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $diaAnterior = date("Y-m-d", strtotime('-1 day', time()));

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="Pedidos.csv"');
        $columns = ['ID', 'Numero de Orden', 'Producto', 'Kilogramos', 'Monto Abonado', 'Notas Cliente', 'Precio Actual'];

        $fp = fopen("PEDIDOS".$diaAnterior, 'w');
        fputcsv($fp, $columns);

        foreach ($this->getOrderCollection() as $order){

            $orderId = $order->getId();

            $orderItems = $order->getAllVisibleItems();

            foreach ($orderItems as $item){
                $itemsData = [];
                $creadoElDia = $order->getCreatedAt();
                if($creadoElDia == $diaAnterior) {
                    $itemsData[] = $orderId;
                    $itemsData[] = $order->getIncrementId();
                    $itemsData[] = $item->getName();
                    $itemsData[] = $item->getQtyOrdered();
                    $itemsData[] = $order->getBaseTotalPaid();
                    $itemsData[] = 'Notas del Cliente';
                    $itemsData[] = $item->getPrice();

                    fputcsv($fp, $itemsData);
                }
            }
        }
        fclose($fp);
    }

    public function getOrderCollection()
    {
        $collection = $this->orderCollectionFactory->create();

        return $collection;
    }
}
