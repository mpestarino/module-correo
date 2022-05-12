<?php
/**
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\Carrier;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Psr\Log\LoggerInterface;
use Tiargsa\CorreoArgentino\Helper\Data;
use Tiargsa\CorreoArgentino\Model\ShippingProcessor;
use Tiargsa\CorreoArgentino\Service\CorreoApiService;

class PickupDelivery extends AbstractCarrier implements CarrierInterface
{
    const CARRIER_CODE = 'correosucursal';
    const METHOD_CODE = 'sucursal';

    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    protected \Magento\Shipping\Model\Tracking\ResultFactory $_trackFactory;

    /**
     * @var StatusFactory
     */
    protected StatusFactory $_trackStatusFactory;

    /**
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    /**
     * @var bool
     */
    protected $_isFixed = true;
    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var Data
     */
    protected $correoHelper;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ShippingProcessor
     */
    protected $shippingProcessor ;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param Data $correoHelper
     * @param Session $checkoutSession
     * @param ShippingProcessor $shippingProcessor
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Data $correoHelper,
        Session $checkoutSession,
        \Tiargsa\CorreoArgentino\Model\ShippingProcessor $shippingProcessor,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        StatusFactory $trackStatusFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->correoHelper = $correoHelper;
        $this->checkoutSession = $checkoutSession;
        $this->shippingProcessor = $shippingProcessor;
        $this->_trackFactory = $trackFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return Result|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var Result $result */
        $result = $this->_rateResultFactory->create();

        $shippingPrice = $this->getShippingPrice($request);

        if ($shippingPrice !== false) {
            $method = $this->createResultMethod($shippingPrice);
            $result->append($method);
        }
        return $result;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * Returns shipping price
     *
     * @param RateRequest $request
     * @return bool|float
     */
    private function getShippingPrice(RateRequest $request)
    {
        $shippingPrice = false;
        if (!$request->getFreeShipping()) {
            $rate = $this->shippingProcessor
                ->getRate(
                    $request->getAllItems(),
                    $request->getDestPostcode(),
                    PickupDelivery::CARRIER_CODE
                );
            if ($rate->getStatus()) {
                $shippingPrice = $rate->getPrice();
            }
            if (!is_bool($shippingPrice)) {
                $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);
            }
        } else {
            $shippingPrice = 0;
        }

        return $shippingPrice;
    }

    /**
     * Creates result method
     *
     * @param int|float $shippingPrice
     * @return Method
     */
    private function createResultMethod($shippingPrice)
    {
        /** @var Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier(self::CARRIER_CODE);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod(self::METHOD_CODE);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        return $method;
    }

    /**
     * @param $tracking_number
     *
     * @return Status
     */
    public function getTrackingInfo($tracking_number)
    {
        $result = $this->_trackFactory->create();
        $tracking = $this->_trackStatusFactory->create();

        $tracking->setCarrier($this->_code);
        $tracking->setCarrierTitle('Correo Argentino');
        $tracking->setTracking($tracking_number);
        $tracking->setUrl($this->correoHelper->getHistoryUrlCorreo(). $tracking_number);

        $result->append($tracking);

        return $tracking;
    }

    public function isTrackingAvailable()
    {
        return true;
    }

    public function isShippingLabelsAvailable()
    {
        return true;
    }
}
