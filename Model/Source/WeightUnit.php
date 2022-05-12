<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WeightUnit implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            'kilos' => 'kg / m3',
            'gramos'=> 'gramos / cm3'
        ];
    }
}
