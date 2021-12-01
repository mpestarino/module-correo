<?php
/**
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\ResultFactory;
use Tiargsa\CorreoArgentino\Helper\Data;
use Tiargsa\CorreoArgentino\Model\ShippingProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Tiargsa\CorreoArgentino\Service\CorreoApiService;

class StandardDelivery extends AbstractCarrier implements CarrierInterface
{
    const CARRIER_CODE = 'correoestandar';
    const METHOD_CODE = 'estandar';
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
     * @param \Psr\Log\LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param ShippingProcessor $shippingProcessor
     * @param Data $correoHelper
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \Tiargsa\CorreoArgentino\Model\ShippingProcessor $shippingProcessor,
        Data $correoHelper,
        array $data = []
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
            $rate = $this->shippingProcessor->getRate($request->getAllItems(), $request->getDestPostcode(), \Tiargsa\CorreoArgentino\Model\Carrier\StandardDelivery::CARRIER_CODE);
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
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createResultMethod($shippingPrice)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier(self::CARRIER_CODE);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod(self::METHOD_CODE);
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        return $method;
    }

//    /**
//     * @param \Magento\Shipping\Model\Shipment\Request $request
//     * @return DataObject
//     * @throws LocalizedException
//     */
//    public function requestToShipment($request)
//    {
//        $response = new DataObject();
//        $packages = $request->getPackages();
//        if (!is_array($packages) || !$packages) {
//            throw new LocalizedException(__('No packages for request'));
//        }
//        $data = [];
//        $errors = [];
//        foreach ($packages as $packageId => $package) {
//            $request->setPackageId($packageId);
//            $request->setPackagingType($package['params']['container']);
//            $request->setPackageWeight($package['params']['weight']);
//            $request->setPackageParams(new \Magento\Framework\DataObject($package['params']));
//            $items = $package['items'];
//            foreach ($items as $itemid => $item) {
//                $items[$itemid]['weight'] = $item['weight'];
//            }
//            $request->setPackageItems($items);
//            $result = $this->shippingProcessor->getLabel();
//            if ($result->hasErrors()) {
//                $errors[] = $result->getErrors();
//            }
//            else{
//                $data[] = [
//                    'label_content' => $result->getLabelContent(),
//                ];
//            }
//        }
//
//        $response->setData($data);
//        if (count($errors) > 0) {
//            $response->setErrors($errors);
//        }
//
//        return $response;
//    }

    public function isTrackingAvailable()
    {
        return true;
    }

    public function isShippingLabelsAvailable()
    {
        return true;
    }
}
