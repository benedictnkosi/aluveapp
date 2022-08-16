<?php

namespace App\Service;

use App\Entity\Property;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class WebScrapperApi
{
    private $em;
    private $logger;
    private $client;
    private $responseArray = array();
    private $nextLinkPresent = true;
    private $property;
    private $listingCount = 0;
    private $pageCounter = 0;
    private $isPriceInt = false;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        $this->client = new Client();
    }

    public function scrapPage($url): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        try{

            $pageLimit = 1000;
            $this->pageCounter = 0;
            while ($this->nextLinkPresent && $this->pageCounter < $pageLimit) {
                $this->pageCounter++;
                $this->logger->info("Page number: " . $this->pageCounter);

                $crawler = $this->client->request('GET', str_replace("page_number", $this->pageCounter,$url));

                $this->nextLinkPresent = false;
                //per listing
                $this->responseArray = array();
                $this->listingCount = 0;
                $crawler->filterXPath('//div[contains(@class,"js_resultTile")][not(contains(@class,"p24_development"))][not(contains(@class,"p24_promotedTile"))]')->each(function (Crawler $parentCrawler, $i) {
                    try{
                        $this->logger->debug("iterating the listing " . $this->listingCount);
                        $this->isPriceInt = false;
                        $this->listingCount++;
                        $this->property = new Property();
                        //price
                        $parentCrawler->filterXPath('//span[@class="p24_price"]')->each(function ($node) {
                            //$this->logger->debug("found price " . $node->text());
                            $this->responseArray[] = array(
                                'price' => $node->text()
                            );
                            $price = str_replace("R", "", $node->text());
                            //$this->logger->debug("semi clean price is " . $price);
                            $price = intval(str_replace(" ", "", $price));
                            if($price !== 0){
                                $this->isPriceInt = true;
                            }
                            // $this->logger->debug("clean price is " . $price);
                            $this->property->setPrice($price);
                        });

                        //bedrooms
                        $parentCrawler->filter('span[title=Bedrooms] > span')->each(function ($node) {
                            $this->responseArray[] = array(
                                'bedroom' => $node->text()
                            );
                            $this->property->setBedrooms(doubleval($node->text()));
                        });

                        //bathrooms
                        $parentCrawler->filter('span[title=Bathrooms] > span')->each(function ($node) {
                            $this->responseArray[] = array(
                                'bathrooms' => $node->text()
                            );
                            $this->property->setBathrooms(doubleval($node->text()));
                        });

                        //parking
                        $parentCrawler->filter('span[title="Parking Spaces"] > span')->each(function ($node) {
                            $this->responseArray[] = array(
                                'parking' => $node->text()
                            );
                            $this->property->setGarage(doubleval($node->text()));
                        });

                        //location
                        $parentCrawler->filterXPath('//span[contains(@class, "p24_location")]')->each(function ($node) {
                            $this->responseArray[] = array(
                                'location' => $node->text()
                            );
                            $this->property->setLocation($node->text());
                        });

                        //link
                        $parentCrawler->filterXPath('//a[contains(@href, "for-sale")]')->each(function ($node) {
                            $this->responseArray[] = array(
                                'link' => "https://www.property24.com" . $node->attr('href')
                            );
                            $this->property->setUrl("https://www.property24.com" . $node->attr('href'));
                        });

                        //link
                        $parentCrawler->filter('span[title="Erf Size"] > span')->each(function ($node) {
                            $this->responseArray[] = array(
                                'Erf Size' => str_replace(" m²", "", $node->text())
                            );

                            $property = $this->em->getRepository(Property::class)->findOneBy(array('url' => $node->text()));
                            if($property !== null){
                                $this->logger->debug("found duplicate url. exiting- " . $node->text());
                                exit();
                            }

                            $erf = str_replace(" m²", "", $node->text());
                            $erf = intval(str_replace(" ", "", $erf));
                            $this->property->setErf($erf);
                        });

                        $this->property->setType('house');
                        $this->property->setPage($this->pageCounter);

                        if (!$this->em->isOpen()) {
                            $this->em = $this->em->create(
                                $this->em->getConnection(),
                                $this->em->getConfiguration()
                            );
                        }
                        if($this->isPriceInt){
                            $this->em->persist($this->property);
                            $this->em->flush($this->property);
                        }


                        // $this->logger->debug(print_r($this->responseArray, true));
                    }catch(\Exception $ex){
                        $this->logger->debug($ex->getMessage());
                    }

                });

                $crawler->filterXPath("//a[contains(text(),'Next')][not(contains(@class,'text-muted'))]")->each(function ($node) {
                    $this->nextLinkPresent = true;
                });
                $this->logger->debug("Sleeping now......");
                sleep(10);
            }
        }catch(\Exception $ex){
            $this->logger->debug($ex->getMessage());
        }


        $responseArray[] = array("results"=>0);
        return $responseArray;
    }


}