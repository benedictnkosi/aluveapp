<?php

namespace App\Service;

use App\Entity\Property;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Google\Client;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Draft;
use Google_Service_Gmail_Message;
use Psr\Log\LoggerInterface;

require_once(__DIR__ . '/../app/application.php');

class CommunicationApi
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }

    public function sendEmailViaGmail($emailFrom, $emailTo, $messageBody, $subject, $propertyName = "Aluve App", $replyTo = null): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        if(str_contains($_SERVER['SERVER_NAME'], "localhost1" )){
            $responseArray[] = array(
                'result_message' => "local host not sending email",
                'result_code' => 1
            );
            $this->logger->debug("local host not sending email");
            return $responseArray;
        }

        try {
            if(!empty($emailTo)){
                $gmailService = $this->createGmailService();
                $messageBody = $this->createMessage($emailFrom, $emailTo, $subject, $messageBody, $propertyName, $replyTo);
                $this->sendGmailMessage($gmailService, $emailFrom, $messageBody);
                $responseArray[] = array(
                    'result_message' => 'Successfully sent message. Thank you',
                    'result_code' => 0
                );

                return $responseArray;
            }else{
                $responseArray[] = array(
                    'result_message' => "email not provided",
                    'result_code' => 1
                );
                $this->logger->debug(print_r($responseArray, true));
            }
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() .' - '. __METHOD__ . ':' . $ex->getLine() . ' ' .  $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error(print_r($responseArray, true));
        }

        $this->logger->debug("Ending Method before the return: " . __METHOD__);
        return $responseArray;
    }

    /**
     * @param $sender string sender email address
     * @param $to string recipient email address
     * @param $subject string email subject
     * @param $messageText string email text
     * @return Google_Service_Gmail_Message
     */
    function createMessage(string $sender, string $to, string $subject, string $messageText, $propertyName, $replyTo = null): Google_Service_Gmail_Message
    {
        $message = new Google_Service_Gmail_Message();
        $rawMessageString = "From: $propertyName <{$sender}>\r\n";
        $rawMessageString .= "To: <{$to}>\r\n";
        if($replyTo != null){
            $rawMessageString .= "Reply-To: <{$replyTo}>\r\n";
        }
        $rawMessageString .= 'Subject: =?utf-8?B?' . base64_encode($subject) . "?=\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
        $rawMessageString .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $rawMessageString .= "{$messageText}\r\n";
        $rawMessage = strtr(base64_encode($rawMessageString), array('+' => '-', '/' => '_'));
        $this->logger->debug("email: " . $rawMessage);
        $message->setRaw($rawMessage);
        return $message;
    }

    /**
     * @param $service Google_Service_Gmail an authorized Gmail API service instance.
     * @param $user string User's email address
     * @param $message Google_Service_Gmail_Message
     * @return Google_Service_Gmail_Draft
     */
    function createDraft($service, $user, $message) {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $draft = new Google_Service_Gmail_Draft();
        $draft->setMessage($message);
        try {
            $draft = $service->users_drafts->create($user, $draft);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() ." ". $e->getTraceAsString());
        }
        $this->logger->debug("Ending Method: " . __METHOD__);
        return $draft;
    }

    /**
     * @param $service Google_Service_Gmail an authorized Gmail API service instance.
     * @param $userId string User's email address
     * @param $message Google_Service_Gmail_Message
     * @return null|Google_Service_Gmail_Message
     */
    function sendGmailMessage($service, $userId, $message) {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            return $service->users_messages->send($userId, $message);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . ' - ' . $e->getTraceAsString());
        }
        $this->logger->debug("Ending Method: " . __METHOD__);
        return null;
    }

    /**
     * @throws \Google\Exception
     */
    public function getAirbnbConfirmationEmails(): ?array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        // Creates and returns the Analytics Reporting service object.

        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        $KEY_FILE_LOCATION = __DIR__ . '/aluve-guesthouse-9f79c476c8c8.json';

        // Create and configure a new client object.
        $client = new Google_Client();
        $client->setApplicationName("Hello Analytics Reporting");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://mail.google.com/']);
        $client->setSubject("admin@aluveapp.co.za");

        $service = new \Google_Service_Gmail($client);
        $responseArray = array();
        try{

            // Print the labels in the user's account.
            $pageToken = null;
            $messages = array();
            $opt_param = array();

            do{
                if($pageToken){
                    $opt_param['pageToken'] = $pageToken;
                }
                $midnight = new DateTimeImmutable('today midnight');
                $timestampOfMidnight = $midnight->getTimestamp();
                $opt_param['q'] = 'subject:Reservation confirmed after:' . $timestampOfMidnight;
                $messagesResponse = $service->users_messages->listUsersMessages("me", $opt_param);
                if($messagesResponse->getMessages()){
                    $messages = array_merge($messages, $messagesResponse->getMessages() );
                    $pageToken = $messagesResponse->getNextPageToken();
                }
            }while ($pageToken);

            foreach ($messages as $message){
                $msg = $service->users_messages->get("admin@aluveapp.co.za",$message->getId());
                $headers = $msg->getPayload()->getHeaders();
                $subject = "";
                foreach ($headers as $header){
                    if(strcmp($header->getName(), "Subject") === 0){
                        $subject = $header->getValue();
                    }

                    // $this->logger->debug("results from google is " .$header->getName() . " - " . $header->getValue());
                }
                $msgData = $msg->getPayload()->getParts()[1]->getBody()->data;

                $out = str_replace("-", "+", $msgData);
                $out = str_replace("_", "/", $out);
                $cleanedMessage = base64_decode($out);
                $responseArray[] = array(
                    'id' => $message->getId(),
                    'subject' =>$subject,
                    'body' =>$cleanedMessage
                );
            }
            //$this->logger->debug("results from google is " .print_r($responseArray));
        }
        catch(Exception $e) {
            // TODO(developer) - handle error appropriately
            $this->logger->debug($e->getMessage());
            $this->logger->debug(print_r($e->getTraceAsString(), true));
            return null;
        }
        return $responseArray;
    }


    /**
     * @throws \Google\Exception
     */
    public function createGmailService(): Google_Service_Gmail
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        // Creates and returns the Analytics Reporting service object.

        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        $KEY_FILE_LOCATION = __DIR__ . '/aluve-guesthouse-9f79c476c8c8.json';

        try{
            // Create and configure a new client object.
            $client = new Google_Client();
            $client->setApplicationName("Hello Gmail");
            $client->setAuthConfig($KEY_FILE_LOCATION);
            $client->setScopes(['https://mail.google.com/']);
            $client->setSubject("admin@aluveapp.co.za");
        }catch(Exception $ex){
            $this->logger->debug($ex->getMessage() . ' - ' . $ex->getTraceAsString());
        }

        $this->logger->debug("Ending Method: " . __METHOD__);
        return new \Google_Service_Gmail($client);
    }

    /**
     * @throws \Google\Exception
     * @throws Exception
     */
    function getClient(): Client
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $client = new Client();
        $client->setApplicationName('Gmail API PHP Quickstart');
        $client->setScopes('https://www.googleapis.com/auth/gmail.addons.current.message.readonly');
        $client->setAuthConfig('credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                //print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

}