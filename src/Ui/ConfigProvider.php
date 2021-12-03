<?php
/**
 * @author Drubu Team
 * @copyright Copyright (c) 2021 Drubu
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Psr\Log\LoggerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Tiargsa\CorreoArgentino\Service\CorreoApiService
     */
    private $apiService;

    protected $logger;

    public function __construct(
        \Tiargsa\CorreoArgentino\Service\CorreoApiService $apiService,
        LoggerInterface $logger
    ) {
        $this->apiService = $apiService;
        $this->logger = $logger;
    }

    public function getConfig()
    {
        $stores = $this->apiService->getLocations();
        $result = [];
        if (is_array($stores)) {
            foreach ($stores as $store) {
                if($store['status'] == "active"){
                    $storeName = $store["location"]['street_name'] . ' ' . $store["location"]['street_number'] . ', CP: ' . $store["location"]['zip_code'] . ', ' .$store["location"]['city_name'] . ', ' . $store['location']['state_name'];
//                    direccion
//                    if ($store['location']['city_name'] == 'CIUDAD AUTONOMA DE BUENOS AIRES') {
//                        $result[$store['location']['state_name']][ucwords(strtolower($store['location']))][$storeName] = $store;
//                    } else {
                        //$result[$store['location']['state_name']][$store['location']['city_name']][$storeName] = $store;
                        $result[$storeName] = $store;
//                    }
                }
            }
        }
        return [
            'correo' => [
                'stores' => $result
            ]
        ];
    }
}
