<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Tiargsa\CorreoArgentino\Helper\Data;
use Tiargsa\CorreoArgentino\Model\ShippingProcessor;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Tiargsa\CorreoArgentino\Service\CorreoApiService;

class PriorityDelivery extends AbstractCarrier implements CarrierInterface
{
    const CARRIER_CODE = 'correourgente';
    const METHOD_CODE = 'urgente';
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
     * @var ShippingProcessor
     */
    protected $shippingProcessor ;

    /**
     * @var Data
     */
    protected $correoHelper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param CorreoApiService $correoApiService
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory         $rateErrorFactory,
        LoggerInterface      $logger,
        ResultFactory        $rateResultFactory,
        MethodFactory        $rateMethodFactory,
        ShippingProcessor    $shippingProcessor,
        Data                 $correoHelper,
        array                $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->shippingProcessor = $shippingProcessor;
        $this->correoHelper = $correoHelper;
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
                    \Tiargsa\CorreoArgentino\Model\Carrier\PriorityDelivery::CARRIER_CODE
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

    public function isTrackingAvailable()
    {
        return true;
    }

    public function isShippingLabelsAvailable()
    {
        return true;
    }
}
