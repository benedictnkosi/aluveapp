<?php

namespace App\Service;

use App\Entity\FlipabilityProperty;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

require_once(__DIR__ . '/../app/application.php');

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
    private $isErfInt = false;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        $this->client = new Client();
    }

    public function scrapPage(): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        try {

            $pageLimit = 1;
            $this->pageCounter = 0;
            while ($this->nextLinkPresent && $this->pageCounter < $pageLimit) {
                $this->pageCounter++;
                $this->logger->info("Page number: " . $this->pageCounter);

                $crawler = $this->client->request('GET', str_replace("page_number", $this->pageCounter, PROPERTY24_URL));

                $this->nextLinkPresent = false;
                //per listing
                $this->responseArray = array();
                $this->listingCount = 0;
                $crawler->filterXPath('//div[contains(@class,"js_resultTile")][not(contains(@class,"p24_development"))][not(contains(@class,"p24_promotedTile"))]')->each(function (Crawler $parentCrawler, $i) {
                    try {
                        $this->logger->debug("iterating the listing " . $this->listingCount);
                        $this->isPriceInt = false;
                        $this->isErfInt = false;
                        $this->listingCount++;
                        $this->property = new FlipabilityProperty();
                        //price
                        $parentCrawler->filterXPath('//span[@class="p24_price"]')->each(function ($node) {
                            //$this->logger->debug("found price " . $node->text());
                            $this->responseArray[] = array(
                                'price' => $node->text()
                            );
                            $price = str_replace("R", "", $node->text());
                            //$this->logger->debug("semi clean price is " . $price);
                            $price = intval(str_replace(" ", "", $price));
                            if ($price !== 0) {
                                $this->isPriceInt = true;
                            }

                            if ($price > MAX_FLIPABILITY_PRICE) {
                                $this->isPriceInt = false;
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

                            $property = $this->em->getRepository(FlipabilityProperty::class)->findOneBy(array('url' => "https://www.property24.com" . $node->attr('href')));
                            if ($property !== null) {
                                $this->logger->debug("found duplicate url. exiting- " . $node->attr('href'));
                                exit();
                            }else{
                                $this->logger->debug("property not found - " . $node->attr('href'));
                            }
                        });

                        //erf
                        $parentCrawler->filter('span[title="Erf Size"] > span')->each(function ($node) {
                            $this->responseArray[] = array(
                                'Erf Size' => str_replace(" m²", "", $node->text())
                            );

                            $erf = str_replace(" m²", "", $node->text());
                            $erf = intval(str_replace(" ", "", $erf));
                            if ($erf !== 0) {
                                $this->isErfInt = true;
                            }

                            if ($erf < MIN_FLIPABILITY_ERF) {
                                $this->isErfInt = false;
                            }

                            $this->property->setErf($erf);
                        });

                        $this->property->setType('house');
                        $this->property->setTimestamp(new DateTime());

                        if (!$this->em->isOpen()) {
                            $this->em = $this->em->create(
                                $this->em->getConnection(),
                                $this->em->getConfiguration()
                            );
                        }
                        if ($this->isPriceInt && $this->isErfInt) {
                            $this->em->persist($this->property);
                            $this->em->flush($this->property);

                            //send email if this meets the flipability score
                            $birdViewApi = new FlipabilityApi($this->em, $this->logger);
                            $locationAverages = $birdViewApi->getLocationAvgERFAndPrice($this->property->getLocation(), $this->property->getBedrooms(), $this->property->getBathrooms());
                            $averagePrice = "";
                            $averageErf = "";
                            foreach ($locationAverages as $locationAverage) {
                                $averagePrice = $locationAverage['price'];
                                $averageErf = $locationAverage['erf'];
                            }

                            $sellingPriceToAvgPriceRatio = intval((intval($this->property->getPrice()) / intval($averagePrice)) * 100);
                            $renovationCost = intval(intval($averagePrice) * 0.2);
                            $maxOfferPrice = (intval($averagePrice) * 0.7) - $renovationCost;
                            $sellingPriceToMaxOfferRatio = intval((intval($maxOfferPrice) / intval($this->property->getPrice())) * 100);

                            $FlipabilityScore = floatval(intval($averagePrice) / intval($this->property->getPrice())) +
                                floatval(intval($this->property->getErf()) / intval($averageErf));

                            if ($sellingPriceToAvgPriceRatio < 70) {
                                $communicationApi = new CommunicationApi($this->em, $this->logger);
                                $message = "<br> Hey you, 
<br><br>looks like we found a potential flip in " . $this->property->getLocation() . " with a score of $FlipabilityScore
            <br>
            <br> Price: R" . number_format((float)$this->property->getPrice(), 0, '.', ' ') . "
            <br> AVG Location Price: R" . number_format((float)$averagePrice, 0, '.', ' ') . "
            <br> Max Offer: R" . number_format((float)$maxOfferPrice, 0, '.', ' ') . "
            <br> Price To Max Offer Ratio: " . number_format((float)$sellingPriceToMaxOfferRatio, 0, '.', ' ') . "
            <br> Price To AVG Price Ratio: " . number_format((float)$sellingPriceToAvgPriceRatio, 0, '.', ' ') . "
            <br> ERF: " . $this->property->getErf() . "
            <br> AVG Location ERF: " . number_format((float)$averageErf, 0, '.', ' ') . "
            <br> Link: " . $this->property->getUrl();
                                $communicationApi->sendEmailViaGmail(ALUVEAPP_ADMIN_EMAIL, "nkosi.benedict@gmail.com", $message, "Flipability - New House", "Aluve Flipability");
                            }
                        }


                        $this->logger->debug(print_r($this->responseArray, true));
                    } catch (\Exception $ex) {
                        $this->logger->debug($ex->getMessage());
                    }

                });

                $crawler->filterXPath("//a[contains(text(),'Next')][not(contains(@class,'text-muted'))]")->each(function ($node) {
                    $this->nextLinkPresent = true;
                });
                $this->logger->debug("Sleeping now......");
                sleep(10);
            }
        } catch (\Exception $ex) {
            $this->logger->debug($ex->getMessage());
        }


        $responseArray[] = array("results" => 0);
        return $responseArray;
    }


}