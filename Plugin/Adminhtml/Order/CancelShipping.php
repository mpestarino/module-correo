<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Plugin\Adminhtml\Order;

use Magento\Backend\Model\UrlInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\OrderRepository;

class CancelShipping
{
    /**
     * @var UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    public function __construct(
        UrlInterface $backendUrl
    ) {
        $this->_backendUrl = $backendUrl;
    }
    public function beforeSetLayout(View $subject)
    {
        $order = $subject->getOrder();
        if (strpos($order->getShippingMethod(), 'correo') !== false && $this->hasTracking($order)) {
            $sendOrder = $this->_backendUrl->getUrl(
                'correo/order/operations/operation/cancel_shipping',
                ['order_id' => $subject->getOrderId()]
            );
            $subject->addButton(
                'cancelShipping',
                [
                    'label' => __('Cancelar Envio Correo'),
                    'onclick' => "setLocation('" . $sendOrder . "')",
                    'class' => 'ship'
                ]
            );
        }

        return null;
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function hasTracking($order)
    {
        $hasTracking = false;
        if ($order->hasShipments()) {
            /**
             * @var Shipment $shipment
             */
            foreach ($order->getShipmentsCollection() as $shipment) {
                foreach ($shipment->getTracksCollection()->getItems() as $track) {
                    return !empty($track->getTrackNumber());
                }
            }
        }
        return $hasTracking;
    }
}
