<?php

namespace App\Helpers;

use mysqli;
use Psr\Log\LoggerInterface;

require_once(__DIR__ . '/../app/application.php');

class DatabaseHelper
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function queryDatabase($sql)
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $conn = new mysqli (DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
        // Check connection
        if ($conn->connect_error) {
            $this->logger->info("failed to connect to the database");
            die ("Connection failed: " . $conn->connect_error);
        }
        $result = $conn->query($sql);
        $conn->close();
        if (!empty($result) && $result->num_rows > 0) {
            $this->logger->info("results found");
            return $result;
        } else {
            $this->logger->info("No results found");
            return false;
        }
    }
}