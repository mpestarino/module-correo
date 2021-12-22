<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Controller\Adminhtml\Order;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Component\MassAction\FilterFactory;
use Tiargsa\CorreoArgentino\Model\ShippingProcessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\OrderRepositoryInterface as SalesOrderRepositoryInterface;
use Magento\Shipping\Model\Shipping\LabelGeneratorFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Zend_Pdf_Exception;

class Operations extends Action
{
    const ADMIN_RESOURCE = 'Tiargsa_CorreoArgentino::shipping_operations';
    const GENERATE_SHIPPING = 'mass_generate_shipping';
    const PRINT_SHIPPING_LABEL = 'print_shipping_label';

    /**
     * @var FilterFactory
     */
    private $filterFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ShippingProcessor
     */
    private $shippingProcessor;

    /**
     * @var LabelGeneratorFactory
     */
    private $_labelGeneratorFactory;

    /**
     * @var FileFactory
     */
    private $_fileFactory;

    /**
     * @var SalesOrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Context $context
     * @param FilterFactory $filterFactory
     * @param CollectionFactory $collectionFactory
     * @param ShippingProcessor $shippingProcessor
     * @param LabelGeneratorFactory $labelGeneratorFactory
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        FilterFactory $filterFactory,
        CollectionFactory $collectionFactory,
        ShippingProcessor $shippingProcessor,
        LabelGeneratorFactory $labelGeneratorFactory,
        FileFactory $fileFactory,
        SalesOrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->filterFactory = $filterFactory;
        $this->collectionFactory = $collectionFactory;
        $this->shippingProcessor = $shippingProcessor;
        $this->_labelGeneratorFactory = $labelGeneratorFactory;
        $this->_fileFactory = $fileFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $operation = $this->getRequest()->getParam('operation');
        $functionName = lcfirst(str_replace('_', '', ucwords($operation, '_')));
        if ($this->{$functionName}()) {
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
        }
    }

    private function massGenerateShipping()
    {
        /**
         * @var Filter $filter
         */
        $filter = $this->filterFactory->create();
        $collection = $filter->getCollection($this->collectionFactory->create());
        $successCount = 0;
        $failureCount = 0;
        $total = $collection->count();
        $failureDetail = [];
        foreach ($collection as $order) {
            $shipmentResult = $this->shippingProcessor->generateCorreoShipping($order);
            if ($shipmentResult->getStatus()) {
                $successCount++;
            } else {
                $failureCount++;
                $failureDemasitail[] = "Order #" . $order->getIncrementId() . ' - ' . $shipmentResult->getMessage();
            }
        }

        //if ($successCount && !$failureCount) {
            $this->messageManager->addSuccessMessage(__('Todos los pedidos se generaron con exito!'));
//        } else {
//            if ($successCount) {
//                $this->messageManager->addSuccessMessage(
//                    __($successCount . '/' . $total . ' pedidos se generaron con exito!')
//                );
//            }
//            $this->messageManager->addErrorMessage(
//                $failureCount . ' pedidos no se generaron con exito. ' . json_encode($failureDetail)
//            );
//        }
        return true;
    }

    private function massPrintShippingLabel()
    {
        /**
         * @var Filter $filter
         */
        $filter = $this->filterFactory->create();
        $collection = $filter->getCollection($this->collectionFactory->create());
        /**
         * @var Order $order
         */
        $labelContent = [];
        foreach ($collection as $order) {
            if ($order->hasShipments()) {

                /**
                 * @var Shipment $shipment
                 */
                foreach ($order->getShipmentsCollection() as $shipment) {
                    foreach ($shipment->getTracksCollection()->getItems() as $track) {
                        $labelContent[] = $this->shippingProcessor->getLabel($track->getTrackNumber());
                    }
                }
            }
        }

        $pdfName        = 'guia_masiva_'.date_timestamp_get(date_create()) . '.pdf';

        if (!empty($labelContent)) {
            $outputPdf = $this->_labelGeneratorFactory->create()->combineLabelsPdf($labelContent);
            return $this->_fileFactory->create(
                $pdfName,
                $outputPdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } else {
            $this->messageManager->addWarningMessage(
                'Los pedidos seleccionados no tienen una etiqueta correo disponible.'
            );
        }
        return true;
    }

    /**
     * @throws Zend_Pdf_Exception
     */
    public function printShippingLabel()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!empty($orderId)) {
            $labelContent = [];
            $order = $this->orderRepository->get($orderId);
            /**
             * @var Shipment $shipment
             */
            foreach ($order->getShipmentsCollection() as $shipment) {
                foreach ($shipment->getTracksCollection()->getItems() as $track) {
                    $base64 = $this->shippingProcessor->getLabel($track->getTrackNumber());
                }
            }

            $pdf_decoded = base64_decode($base64['fileBase64']);
            array_push($labelContent, $pdf_decoded);
            $outputPdf = $this->_labelGeneratorFactory->create()->combineLabelsPdf($labelContent);
            return $this->_fileFactory->create(
                $base64['filename'],
                $outputPdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );

        }
        return true;
    }

    public function cancelShipping()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!empty($orderId)) {
            $cancelShipping = false;
            $order = $this->orderRepository->get($orderId);
            /**
             * @var Shipment $shipment
             */
            foreach ($order->getShipmentsCollection() as $shipment) {
                foreach ($shipment->getTracksCollection()->getItems() as $track) {
                    $cancelShipping = $this->shippingProcessor->cancelShipping($track->getTrackNumber());
                }
            }

            if ($cancelShipping) {
                $this->messageManager->addSuccessMessage(
                    'El envio se cancelo con exito.'
                );
                $cancelResult = new \Magento\Framework\DataObject;
                $cancelResult->setMessage('Se cancelo el envio con exito');
            } else {
                $this->messageManager->addWarningMessage(
                    'La cancelacion del envio falló.'
                );
            }
        }
        return true;
    }
}
