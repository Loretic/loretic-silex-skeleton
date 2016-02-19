<?php

namespace Loretic\Core\Service;

use GuzzleHttp;

class BaseApiRestService
{

    private $loginName;
    private $password;
    private $companyKey;

    private $client;
    private $baseUrl;

    public function __construct($companyKey, $loginName, $password)
    {
        $this->companyKey = $companyKey;
        $this->loginName = $loginName;
        $this->password = $password;

        $this->client = new GuzzleHttp\Client([
            'auth' => [$this->companyKey.'\\'.$this->loginName,  $this->password]
            , 'Accept' => "application/json"
            // Base URI is used with relative requests
            //, 'base_uri' => 'http://httpbin.org'
            // You can set any number of default request options.
            //, 'timeout'  => 2.0,
            , 'verify' => false
        ]);

        $this->getBaseUrlWSReplicon();
    }

    private function call($url, $method = 'get', $data = null)
    {
        $response = '';
        switch ($method) {
            case 'get' :
                $response = $this->client->get($url);
                break;
            case 'post' :
                //$body_array = array();

                $response = $this->client->post($url, [
                    'form_params' => $data
                ]);

                $response = $this->client->post($url, [
                    'json' => $data
                ]);

                break;
            case 'put' :
                $response = $this->client->put($url, $data);
                break;
            case 'delete' :
                $response = $this->client->delete($url);
                break;
        }

        $dataResponse = new \stdClass();
        $dataResponse->code = $response->getStatusCode();
        $statusCode = $response->getStatusCode();
        //echo $statusCode.'<br>';;
        $data = array();
        if (200 === $statusCode) {
            $data = json_decode($response->getBody());
            if(!empty($data)) {
                $dataResponse->data = $data->d;
            }
        } elseif (201 === $statusCode) {
            $data = json_decode($response->getBody());
            $dataResponse->data = $data->d;
        } elseif (404 === $statusCode){
            $dataResponse->data = json_decode($data);
        } else {
            throw new MyException("Invalid response from api...");
        }

        return $dataResponse;
    }

}