<?php namespace tlorentzen\ThetisPack;

/**
 * Copyright (C) 2017 Thomas Lorentzen
 */
class ThetisPack{

    private $_hostUrl     = "https://public.thetis-pack.com/rest/";
    private $_accessToken = null;
    private $_contextName = null;

    function __construct($accessToken=null, $contextName=null) {
        $this->_accessToken = $accessToken;
        $this->_contextName = $contextName;
    }

    public function setAccessToken($accessToken){
        $this->_accessToken = $accessToken;
    }

    public function setContextName($contextName){
        $this->_contextName = $contextName;
    }

    public function getShipments($parameters=array())
    {
        return $this->doRequest('shipments', $parameters);
    }

    public function getShipmentById($id)
    {
        return $this->doRequest('shipments/'.$id);
    }

    public function getShipmentByOrderId($orderId)
    {
        $parameters = array();
        $parameters['shipmentNumberMatch'] = $orderId;

        $result = $this->doRequest('shipments', $parameters);

        if(!is_array($result) OR count($result) != 1){
            return false;
        }

        return $result[0];
    }

    private function doRequest($resource, $parameters=array(), $type='GET')
    {
        if($this->_accessToken == null OR $this->_accessToken == ""){
            die("Thetis accessToken required.");
        }

        if(is_array($parameters) AND count($parameters) > 0){
            $parameters = implode('&', array_map(
                function ($v, $k) {
                    return $k.'='.$v;
                },
                $parameters,
                array_keys($parameters)
            ));
        }else{
            $parameters = false;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->_hostUrl.$resource.(($parameters AND $type=='GET') ? '?'.$parameters : ''),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $type,
            CURLOPT_POSTFIELDS     => (($type == 'POST' OR $type == 'PUT') ? $parameters : ""),
            CURLOPT_HTTPHEADER     => array(
                "content-type: application/json",
                "thetisaccesstoken: " . $this->_accessToken,
                "thetiscontextname: " . $this->_contextName
            )
        ));

        $productsResponse = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return false;
        }

        $productsResponse = json_decode($productsResponse, true);
        return $productsResponse;
    }

}