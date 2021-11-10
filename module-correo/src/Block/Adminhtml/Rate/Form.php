<?php
/**
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Block\Adminhtml\Rate;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Tiargsa\CorreoArgentino\Model\RateFactory
     */
    protected $_tarifaFactory;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Tiargsa\CorreoArgentino\Model\RateFactory $tarifaFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Tiargsa\CorreoArgentino\Model\RateFactory $tarifaFactory,
        array $data = []
    ) {
        $this->_tarifaFactory = $tarifaFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $zonaId = null;
        $tarifas = $this->_tarifaFactory->create()->getCollection();

        $tarifas->getSelect()
            ->join(
                ['z' => $tarifas->getTable('Tiargsa_CorreoArgentino_zona')],
                'main_table.zona_id = z.zona_id',
                ['nombre_zona'=>'z.nombre']);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        foreach ($tarifas as $_tarifa)
        {
            if($zonaId != $_tarifa->getZonaId())
            {
                $fieldsetEstandar = $form->addFieldset(
                    "zona_{$_tarifa->getZonaId()}_estandar",
                    ['legend' => __("{$_tarifa->getNombreZona()} - Correo Estandar"), 'class' => 'fieldset-wide']
                );
                $fieldsetUrgente  = $form->addFieldset(
                    "zona_{$_tarifa->getZonaId()}_urgente",
                    ['legend' => __("{$_tarifa->getNombreZona()} - Correo Urgente"), 'class' => 'fieldset-wide']
                );
                $fieldsetSucursal = $form->addFieldset(
                    "zona_{$_tarifa->getZonaId()}_sucursal",
                    ['legend' => __("{$_tarifa->getNombreZona()} - Correo Sucursal"), 'class' => 'fieldset-wide']
                );
            }

            $fieldsetEstandar->addField(
                "tarifa_estandar_{$_tarifa->getTarifaId()}",
                'text',
                [
                    'name'      => "tarifa[{$_tarifa->getTarifaId()}][valor_estandar]",
                    'label'     => __("{$_tarifa->getRango()} gr"),
                    'title'     => __("{$_tarifa->getRango()} gr"),
                    'required'  => true,
                    'value'     => $_tarifa->getValorEstandar()
                ]
            );

            $fieldsetUrgente->addField(
                "tarifa_urgente_{$_tarifa->getTarifaId()}",
                'text',
                [
                    'name'      => "tarifa[{$_tarifa->getTarifaId()}][valor_urgente]",
                    'label'     => __("{$_tarifa->getRango()} gr"),
                    'title'     => __("{$_tarifa->getRango()} gr"),
                    'required'  => true,
                    'value'     => $_tarifa->getValorUrgente()
                ]
            );

            $fieldsetSucursal->addField(
                "tarifa_sucursal_{$_tarifa->getTarifaId()}",
                'text',
                [
                    'name'      => "tarifa[{$_tarifa->getTarifaId()}][valor_sucursal]",
                    'label'     => __("{$_tarifa->getRango()} gr"),
                    'title'     => __("{$_tarifa->getRango()} gr"),
                    'required'  => true,
                    'value'     => $_tarifa->getValorSucursal()
                ]
            );

            $zonaId = $_tarifa->getZonaId();
        }

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}