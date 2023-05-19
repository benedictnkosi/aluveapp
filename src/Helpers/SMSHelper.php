<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
require_once(__DIR__ . '/../app/application.php');

class SMSHelper
{

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    function sendMessage ($phoneNumber, $message): bool
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->logger->debug("remote server is " . $_SERVER['HTTP_HOST']);
        $this->logger->debug("phone number " . $phoneNumber);
        $this->logger->debug("message " . $message);

        if (strcmp( $_SERVER['HTTP_HOST'], 'aluveapp.co.za' )===0 ) {
            //Retrieve your API Credentials
            $apiKey = SMS_API_KEY;
            $apiSecret = SMS_API_SECRET;
            $accountApiCredentials = $apiKey . ':' .$apiSecret;

            // Convert to Base64 Encoding
            $base64Credentials = base64_encode($accountApiCredentials);
            $authHeader = 'Authorization: Basic ' . $base64Credentials;

            // Generate an AuthToken
            $authEndpoint = 'https://rest.smsportal.com/Authentication';

            $authOptions = array(
                'http' => array(
                    'header'  => $authHeader,
                    'method'  => 'GET',
                    'ignore_errors' => true
                )
            );
            $authContext  = stream_context_create($authOptions);

            $result = file_get_contents($authEndpoint, false, $authContext);

            $authResult = json_decode($result);


            //Authentication Request
            $status_line = $http_response_header[0];
            preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1];

            if ($status === '200') {
                $authToken = $authResult->{'token'};
            }
            else {
                $this->logger->debug("Failed: " . print_r($authResult, true));
                return false;
            }

            // Send Request
            $sendUrl = 'https://rest.smsportal.com/bulkmessages';

            $authHeader = 'Authorization: Bearer ' . $authToken;

            $sendData = '{ "messages" : [ { "content" : "'.$message.'", "destination" : "'.$phoneNumber.'" } ] }';

            $options = array(
                'http' => array(
                    'header'  => array("Content-Type: application/json", $authHeader),
                    'method'  => 'POST',
                    'content' => $sendData,
                    'ignore_errors' => true
                )
            );
            $context  = stream_context_create($options);

            $sendResult = file_get_contents($sendUrl, false, $context);

            //Response Validation
            $status_line = $http_response_header[0];

            preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1];

            if ($status === '200') {
                $this->logger->info("Success: " . print_r($sendResult, true));
                return true;
            }
            else {
                $this->logger->error("Failed: " . print_r($sendResult, true));
                return false;
            }

        }else{
            $this->logger->debug("Server not in white list " . $_SERVER['REMOTE_ADDR']);
            return true;
        }
    }

}