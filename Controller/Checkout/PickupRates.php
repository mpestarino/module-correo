<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Controller\Checkout;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\CsrfAwareActionInterface;

class PickupRates implements ActionInterface, CsrfAwareActionInterface
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var \Tiargsa\CorreoArgentino\Model\ShippingProcessor
     */
    private $shippingProcessor;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        CheckoutSession $checkoutSession,
        \Tiargsa\CorreoArgentino\Model\ShippingProcessor $shippingProcessor,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    )
    {
        $this->request = $context->getRequest();
        $this->resultFactory = $resultFactory;
        $this->checkoutSession = $checkoutSession;
        $this->shippingProcessor = $shippingProcessor;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $request = $this->request;
        $status = true;
        $storeId = $request->getParam('store_id') ? $request->getParam('store_id') : null;
        $addressZip = $request->getParam('address_zip') ? $request->getParam('address_zip') : null;
        $storeName = $request->getParam('store_name') ? $request->getParam('store_name') : null;
        $rate = $this->shippingProcessor->getRate($this->checkoutSession->getQuote()->getAllItems(), $addressZip, \Tiargsa\CorreoArgentino\Model\Carrier\PickupDelivery::CARRIER_CODE, $storeId);

        if ($this->checkoutSession->getFreeShipping()) {
            $price = 0;
        } else {
            $price = $rate->getPrice();
        }
        $status = $rate->getStatus();
        if ($status) {
            $quote = $this->quoteRepository->getActive($this->checkoutSession->getQuoteId());
            $quote->setCodigoSucursalCorreo($storeId);
            $this->quoteRepository->save($quote);
            $this->checkoutSession->setNombreCorreoSucursal($storeName);
            $this->checkoutSession->setCotizacionCorreoSucursal($rate->getPrice());
        }
        $jsonResponse = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $jsonResponse->setData(['price' => $price, 'status' => $status]);

        return $jsonResponse;
    }

    public function validateForCsrf(RequestInterface $request): ? bool
    {
        return true;
    }

    public function createCsrfValidationException(RequestInterface $request): ? \Magento\Framework\App\Request\InvalidRequestException
    {
        return null;
    }
}