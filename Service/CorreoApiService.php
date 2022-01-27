<?php
/**
 * @author Tiarg Team
 * @copyright Copyright (c) 2021 Tiarg
 * @package Tiargsa_CorreoArgentino
 */

namespace Tiargsa\CorreoArgentino\Service;

use Laminas\Http\Request as RequestAlias;
use Tiargsa\CorreoArgentino\Helper\Data as correoHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Log\LoggerInterface;

class CorreoApiService
{
    /**
     * @var correoHelper
     */
    private $helper;

    /**
     * @var string
     */
    private $token;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        correoHelper $data,
        LoggerInterface $logger
    ) {
        $this->helper = $data;
        $this->logger = $logger;
    }

    public function login()
    {
        $username = $this->helper->getUsername();
        $password = $this->helper->getPassword();
        $response = $this->doRequest($this->helper->getLoginUrl(), [
            'body' => [
                'user' => $username,
                'password'=>$password
            ],
            'header' => [
                'Content-Type: text/plain',
            ]
        ], Request::HTTP_METHOD_POST);
        if ($response->getStatusCode() == 200) {
            $this->token = '';

            if (isset($response->getValue()['token'])) {
                $this->token = $response->getValue()['token'];
                return $this->token;
            }
        }
        return $response->getReason();
    }

    /**
     * @param $tracking
     * @return DataObject
     */
    public function getShippingHistory($tracking)
    {
        if (empty($this->token)) {
            $this->login();
        }

        $shippingByNumber =  $this->helper->getShippingByNumberUrl() . $tracking;
        return $this->getDataFromResponse($this->doRequest(
            $shippingByNumber,
            [
                'header' => [
                    'Authorization: Bearer ' . $this->token
                ]
            ]
        ));
    }

    /**
     * @return DataObject
     */
    public function getCancel($tracking)
    {
        if (empty($this->token)) {
            $this->login();
        }

        $cancelUrl = $this->helper->getCancelUrl(). $tracking . "/cancel";
        return $this->getDataFromResponse($this->doRequest(
            $cancelUrl,
            [
                'header' => [
                    'Authorization: Bearer ' . $this->token
                ]
            ],
            RequestAlias::METHOD_PATCH
        ));
    }

    /**
     * @return DataObject
     */
    public function getLocations()
    {
        $locationUrl = $this->helper->getLocationUrl();

        if (empty($this->token)) {
            $this->login();
        }
        return $this->getDataFromResponse($this->doRequest(
            $locationUrl,
            [
                'header' => [
                    'Authorization: Bearer ' . $this->token
                ]
            ]
        ));
    }

    /**
     * @param DataObject $data
     * @return DataObject
     */
    public function getRates(DataObject $data)
    {
        if (empty($this->token)) {
            $this->login();
        }
        return $this->getDataFromResponse($this->doRequest(
            $this->helper->getRatesUrl(),
            [
                'body' => $data->getData(),
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ]
            ],
            Request::HTTP_METHOD_POST
        ));
    }

    /**
     * @param DataObject $data
     * @return DataObject
     */
    public function createOrder(DataObject $data)
    {
        if (empty($this->token)) {
            $this->login();
        }
        return $this->getDataFromResponse($this->doRequest($this->helper->getCreateOrderUrl(), [
            'body' => $data->getData(),
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ]
        ], Request::HTTP_METHOD_POST));
    }

    /**
     * @param string $tracking
     * @return DataObject
     */
    public function getLabel(string $tracking)
    {
        $labelUrl = $this->helper->getLabelUrl();

        if (empty($this->token)) {
            $this->login();
        }
        $params = [
            [
                "sellerId" => '',
                "trackingNumber" => $tracking
            ]
        ];
        return $this->getDataFromResponse($this->doRequest(
            $labelUrl,
            [
                'body' => $params,
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ]
            ],
            Request::HTTP_METHOD_POST
        ));
    }

    /**
     * @param DataObject $response
     * @return DataObject
     */
    private function getDataFromResponse(DataObject $response)
    {
        if ($response->getStatusCode() == 200) {
            return $response->getValue();
        }
        return $response->getReason();
    }

    /**
     * @param $uri
     * @param array $params
     * @param string $requestMethod
     * @param bool $parseToArray
     * @return DataObject
     */
    private function doRequest(
        $uri,
        $params = [],
        $requestMethod = Request::HTTP_METHOD_GET,
        $parseToArray = true
    ) {
        $response = new DataObject();
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $uri,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $requestMethod,
        ]);
        if (isset($params['header'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $params['header']);
        }
        if (isset($params['body'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params['body']));
        }

        $lala = curl_getinfo($curl, CURLOPT_HTTPHEADER);
        $curlResponse = curl_exec($curl);

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        if ($statusCode < 200 || $statusCode >= 300) {
            $response->setStatusCode($statusCode);
            $response->setReason(strval($curlResponse));
        } else {
            $response->setStatusCode(200);
            $response->setValue(json_decode($curlResponse, true));
            $response->setHeaders($params['header']);
        }
        return $response;
    }
}
