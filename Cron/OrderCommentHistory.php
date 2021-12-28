<?php

namespace Tiargsa\CorreoArgentino\Cron;

use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Tiargsa\CorreoArgentino\Service\CorreoApiService;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class OrderCommentHistory
{
    /**
     * @var CorreoApiService
     */
    protected $apiService;

    /**
     * @var CollectionFactory
     */
    protected $orderFactory;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    protected $orderStatusRepository;

    public function __construct(
        CorreoApiService $apiService,
        CollectionFactory $orderFactory,
        OrderStatusHistoryRepositoryInterface $orderStatusRepository
    ) {
        $this->apiService   = $apiService;
        $this->orderFactory = $orderFactory;
        $this->orderStatusRepository = $orderStatusRepository;
    }

    public function execute()
    {

        $order = $this->orderFactory->create();
        $ordersShipping = $order->addFieldToFilter('shipping_method', [
            'correoestandar_estandar',
            'correosucursal_sucursal',
            'correourgente_urgente'
        ]);

        foreach ($ordersShipping as $orderShipping) {
            $commentHistory = $orderShipping->getStatusHistoryCollection()->getFirstItem()->getComment();
            foreach ($orderShipping->getShipmentsCollection() as $shipment) {
                foreach ($shipment->getTracksCollection()->getItems() as $track) {

                    $shippingHistory = $this->apiService->getShippingHistory($track->getTrackNumber());

                    if (!isset($shippingHistory[0]['event'][0]['status'])) {
                        continue;
                    }

                    if ($shippingHistory[0]['event'][0]['statusId'] == "ENT") {
                        $comment = $orderShipping->addStatusHistoryComment(
                            'Estado del Envio: Entregado a '.
                            $shippingHistory[0]['event'][0]['sign']
                        );
                        if ($comment->getComment() == $commentHistory) {
                            continue;
                        }

                    } else {
                        $comment = $orderShipping->addStatusHistoryComment(
                            'Estado del Envio: '.
                            $shippingHistory[0]['event'][0]['status'] .
                            " - " .
                            $shippingHistory[0]['event'][0]['sign']
                        );

                        if ($comment->getComment() == $commentHistory) {
                            continue;
                        }
                    }
                    $this->orderStatusRepository->save($comment);
                }
            }
        }
    }
}
