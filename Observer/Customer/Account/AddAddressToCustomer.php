<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Observer\Customer\Account;

use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class AddAddressToCustomer implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    private $addressRepository;

    private $order;

    /**
     * SalesOrderPlaceBefore constructor.
     * @param \Magento\Customer\Model\Session $customer
     * @param Session $checkoutSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customer,
        Session $checkoutSession,
        AddressRepositoryInterface $addressRepository,
        Order $order
    ) {
        $this->_customerSession = $customer;
        $this->_checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
        $this->order = $order;
    }

    /**
     * Graba en la orden el numero de sucursal correo que tenga el quote, y el dni en el quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $order = $this->order
            ->getCollection()
            ->addFieldToFilter(
                'customer_id',
                $customer->getId()
            )->setOrder('created_at', 'DESC')
            ->getFirstItem();
        if (!empty($order->getId())) {
            $shippingAddress = $order->getShippingAddress();

            //Guardo los atributos custom en la direccion del usuario
            $customerAddress = $this->addressRepository->getById($customer->getDefaultShipping());
            $hasChange = false;
            $customAttributes = $customerAddress->getCustomAttributes();
            if (!array_key_exists('altura', $customAttributes) || array_key_exists('altura', $customAttributes) && $customAttributes['altura']->getValue() != $shippingAddress->getAltura()) {
                $customerAddress->setCustomAttribute('altura', trim($shippingAddress->getAltura()));
                $hasChange = true;
            }
            if (!array_key_exists('dni', $customAttributes) || array_key_exists('dni', $customAttributes) && $customAttributes['dni']->getValue() != $shippingAddress->getDni()) {
                $customerAddress->setCustomAttribute('dni', trim($shippingAddress->getDni()));
                $hasChange = true;
            }
            if (!array_key_exists('piso', $customAttributes) || array_key_exists('piso', $customAttributes) && $customAttributes['piso']->getValue() != $shippingAddress->getPiso()) {
                $customerAddress->setCustomAttribute('piso', trim($shippingAddress->getPiso()));
                $hasChange = true;
            }
            if (!array_key_exists('departamento', $customAttributes) || array_key_exists('departamento', $customAttributes) && $customAttributes['departamento']->getValue() != $shippingAddress->getDepartamento()) {
                $customerAddress->setCustomAttribute('departamento', trim($shippingAddress->getDepartamento()));
                $hasChange = true;
            }
            if (!array_key_exists('observaciones', $customAttributes) || array_key_exists('observaciones', $customAttributes) && $customAttributes['observaciones']->getValue() != $shippingAddress->getObservaciones()) {
                $customerAddress->setCustomAttribute('observaciones', trim($shippingAddress->getObservaciones()));
                $hasChange = true;
            }
            if (!array_key_exists('celular', $customAttributes) || array_key_exists('celular', $customAttributes) && $customAttributes['celular']->getValue() != $shippingAddress->getCelular()) {
                $customerAddress->setCustomAttribute('celular', trim($shippingAddress->getCelular()));
                $hasChange = true;
            }

            if ($hasChange) {
                $this->addressRepository->save($customerAddress);
            }
        }
        return $this;
    }
}
