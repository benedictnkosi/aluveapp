<?php

namespace App\Helpers;

use Psr\Log\LoggerInterface;

class SMSHelper
{

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    function sendMessage ($phoneNumber, $message): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://rest.smsportal.com/v1/BulkMessages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"sendOptions\":{\"duplicateCheck\":\"none\",\"campaignName\":\"aluve\",\"testMode\":false},\"messages\":[{\"landingPageVariables\":{\"variables\":{},\"landingPageId\":\"1\"},\"content\":\"".$message."\",\"destination\":\"".$phoneNumber."\"}]}",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Authorization: BASIC OTc1M2U4ZGYtNWUzYy00ZjY3LTlmMDUtM2I5MDBhNTRiZjkzOmdmNHdTb3gxMno3V2xVZ3FXZ0FPd3NyNWRHVit6Q3Iv",
                "Content-Type: text/json"
            ],
        ]);

        $output = array();

        $whitelist = array( 'aluveapp.co.za', '::1' );
        // check if the server is in the array
        if (in_array( $_SERVER['REMOTE_ADDR'], $whitelist ) ) {
            $output['server_response'] = curl_exec( $curl );
        }else{
            $this->logger->debug("Server not in white list " . $_SERVER['REMOTE_ADDR']);
        }

        $curl_info = curl_getinfo( $curl );
        $output['http_status'] = $curl_info[ 'http_code' ];
        $output['error'] = curl_error($curl);

        curl_close($curl);
        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $output;

    }



}