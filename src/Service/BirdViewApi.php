<?php

namespace App\Service;

use App\Entity\Cleaning;
use App\Entity\FlipabilityProperty;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class BirdViewApi
{

    private $em;
    private $logger;
    private $percentilePrice;
    private $percentileCount;
    private $averageErf;
    private $averagePrice;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
    }


    public function getHomePageSummary($type, $percentageCheaper, $bedrooms, $bathrooms, $erf): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        $propertiesArray = array();
        try {
            $query = $this->em->createQuery("SELECT p, count(p.id) as count, avg(p.price) as price, avg(p.erf) as erf
            FROM App\Entity\FlipabilityProperty p
            where  p.bedrooms > " . $bedrooms . "
             and p.bathrooms > " . $bathrooms . "
            GROUP BY p.location
            ORDER BY count desc ");
            $properties = $query->getResult();


            $htmlTable = "<table><tr>
    <th>Location</th>
    <th>AVG Price</th>
    <th>AVG ERF Size</th>
    <th>Total Properties</th>
    <th>Flippable</th>
  </tr>";

            if (count($properties) > 0) {
                foreach ($properties as $property) {
                    if (strcmp($type, 'average') === 0) {
                        $FlipableProperties = $this->getPropertiesPricedLessThanAVG($property[0]->getLocation(), $percentageCheaper, $bedrooms, $bathrooms, $erf, $property['erf']);
                    } else {
                        $FlipableProperties = $this->getPropertiesPricedLessThanPercentile($property[0]->getLocation(), $type, $percentageCheaper, $bedrooms, $bathrooms, $erf, $property['erf']);
                    }

                    $this->logger->debug("after calling FlipableProperties");

                    $propertiesArray[] = array(
                        'location' => $property[0]->getLocation(),
                        'avg_price' => $property['price'],
                        'avg_erf' => $property['erf'],
                        'count' => $property['count'],
                        'flipable' => count($FlipableProperties),
                    );

                    $this->logger->debug("after creating propertiesArray");

                    $sort = array();
                    foreach ($propertiesArray as $k => $v) {
                        $sort['flipable'][$k] = $v['flipable'];
                        $sort['location'][$k] = $v['location'];
                    }
                    # sort by event_type desc and then title asc
                    array_multisort($sort['flipable'], SORT_DESC, $sort['location'], SORT_ASC, $propertiesArray);

                    $this->logger->debug("after sorting");

                }

                foreach ($propertiesArray as $property) {
                    $this->logger->debug("creating row for " . $property['location']);
                    if (intval($property['flipable']) > 0) {
                        $htmlTable .= '
                      <tr>
                        <td><a target="_blank" href="https://www.google.com/maps/place/' . $property['location'] . '">' . $property['location'] . '</a></td>
                        <td>R' . number_format((float)$property['avg_price'], 0, '.', ' ') . '</td>
                         <td>' . number_format((float)$property['avg_erf'], 0, '.', ' ') . '</td>
                        <td>' . $property['count'] . '</td>
                        <td><a target="_blank" href="/location/' . $property['location'] . '/' . $type . '/' . $percentageCheaper . '/' . $bedrooms . '/' . $bathrooms . '/' . $erf . '/' . $property['avg_erf'] . '">' . $property['flipable'] . '</a></td>
                     
                      </tr>';
                    }
                }

                $this->logger->debug("after creting table row: " . $htmlTable);

                $htmlTable .= '</table>';
                $responseArray[] = array(
                    'html' => $htmlTable,
                    'result_code' => 0
                );
            } else {
                $responseArray[] = array(
                    'result_message' => "No Data",
                    'result_code' => 1
                );
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
        }

        return $responseArray;
    }

    public function getPropertiesPricedLessThanAVG($location, $percentageCheaper, $bedrooms, $bathrooms, $erf, $avgErfSize): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            $query = $this->em->createQuery("SELECT p, avg(p.price) as price, avg(p.erf) as erf
            FROM App\Entity\FlipabilityProperty p
            where p.location = '" . str_replace("'", "''", $location) . "'
            and p.bedrooms > " . $bedrooms . "
            and p.bathrooms > " . $bathrooms . "
            GROUP BY p.location");
            $this->logger->debug("before running query");
            $properties = $query->getResult();
            $this->logger->debug("after running query");

            $avgPrice = "";
            foreach ($properties as $property) {
                $avgPrice = $property['price'];
                $this->averageErf = $property['erf'];
                $this->averagePrice = $property['price'];
            }

            $this->logger->debug("after looping properties avg price" . $avgPrice);
            $minErfSize = intval($avgErfSize) * (1 - floatval($erf));
            $maxPrice = intval($avgPrice) * (1 - (doubleval($percentageCheaper)));
            $query = $this->em->createQuery('SELECT p
            FROM App\Entity\FlipabilityProperty p
            where p.price < ' . $maxPrice . '
            and p.erf > ' . $minErfSize . "
            and p.location = '" . str_replace("'", "''", $location) . "'
            and p.bedrooms > " . $bedrooms . "
            and p.bathrooms > " . $bathrooms . "
            order by p.erf ");
            $this->logger->debug("before running query 2");

            return $query->getResult();

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
            return $responseArray;
        }
    }

    public function getPropertiesPricedLessThanPercentile($location, $filterPercentile, $percentageCheaper, $bedrooms, $bathrooms, $erf, $avgErfSize): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $query = $this->em->createQuery("SELECT p
            FROM App\Entity\FlipabilityProperty p
            where p.location = '" . str_replace("'", "''", $location) . "'
            and p.bedrooms > " . $bedrooms . "
            and p.bathrooms > " . $bathrooms);
            $properties = $query->getResult();
            $priceArray = array();
            foreach ($properties as $property) {
                //$this->logger->debug("adding price to array: " . $property->getPrice());
                $priceArray[] = array(
                    'price' => $property->getPrice(),
                    'erf' => $property->getErf()
                );
            }

            $sort = array();
            foreach ($priceArray as $k => $v) {
                $sort['price'][$k] = $v['price'];
                $sort['erf'][$k] = $v['erf'];
            }
            # sort by event_type desc and then title asc
            array_multisort($sort['price'], SORT_ASC, $sort['erf'], SORT_ASC, $priceArray);
            $arrayCount = count($priceArray);
            $percentageToAply = ((100 - intval($filterPercentile)) / 100);
            $xStart = intval($arrayCount * $percentageToAply);
            $averageErf = 0;
            $i = 0;

            $percentile = 0;
            for ($x = $xStart; $x < $arrayCount; $x++) {
                $averageErf += intval($priceArray[$x]['erf']);
                if ($i < 1) {
                    $percentile = intval($priceArray[$x]['price']);
                }
                $i++;
            }

            $this->averageErf = $averageErf / $i;
            $this->percentileCount = $i;
            $this->percentilePrice = $percentile;
            $maxPrice = floatval($percentile) * (1 - doubleval($percentageCheaper));

            $minErfSize = intval($avgErfSize) * (1 - floatval($erf));

            $query = $this->em->createQuery("SELECT p
            FROM App\Entity\FlipabilityProperty p
            where p.price < " . $maxPrice . " 
            and p.erf > " . $minErfSize . "
            and p.location = '" . str_replace("'", "''", $location) . "'
            and p.bedrooms > " . $bedrooms . "
            and p.bathrooms > " . $bathrooms . "
            order by p.erf ");
            return $query->getResult();

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
            return $responseArray;
        }
    }

    function get_percentile($percentile, $array)
    {
        sort($array);
        $index = ($percentile / 100) * count($array);
        if (floor($index) == $index) {
            $result = ($array[$index - 1] + $array[$index]) / 2;
        } else {
            $result = $array[floor($index)];
        }
        return $result;
    }

    public function getLocationFlipableProperties($location, $type, $percentageCheaper, $bedrooms, $bathrooms, $erf, $avgErf): array|string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        $propertiesArray = array();
        try {
            if (strcmp($type, 'average') === 0) {
                $properties = $this->getPropertiesPricedLessThanAVG($location, $percentageCheaper, $bedrooms, $bathrooms, $erf, $avgErf);
            } else {
                $properties = $this->getPropertiesPricedLessThanPercentile($location, $type, $percentageCheaper, $bedrooms, $bathrooms, $erf, $avgErf);
            }

            $htmlTable = '<table><tr>
    <th>Price</th>
    <th>ERF &#8593</th>
    <th>Bedrooms</th>
    <th>Bathrooms</th>
    <th>Parking</th>
    <th>Link</th>
  </tr>';

            $this->logger->info("before checking property count");
            if (count($properties) > 0) {
                $this->logger->info("count is greater than zero");
                foreach ($properties as $property) {
                    $propertiesArray[] = array(
                        'price' => $property->getPrice(),
                        'erf' => $property->getErf(),
                        'bedrooms' => $property->getBedrooms(),
                        'bathrooms' => $property->getBathrooms(),
                        'parking' => $property->getGarage(),
                        'url' => $property->getUrl(),
                    );

                    $sort = array();
                    foreach ($propertiesArray as $k => $v) {
                        $sort['price'][$k] = $v['price'];
                        $sort['erf'][$k] = $v['erf'];
                    }
                    # sort by event_type desc and then title asc
                    array_multisort($sort['erf'], SORT_DESC, $sort['price'], SORT_ASC, $propertiesArray);

                }

                $this->logger->info("looping propertiesArray");

                foreach ($propertiesArray as $property) {
                    $htmlTable .= '
                      <tr>
                      <td>R' . number_format((float)$property['price'], 0, '.', ' ') . '</td>
                        <td>' . $property['erf'] . '</td>
                        <td>' . $property['bedrooms'] . '</td>
                        <td>' . $property['bathrooms'] . '</td>
                        <td>' . $property['parking'] . '</td>
                        <td><a href="' . $property['url'] . '" target="_blank">Link</a></td>';
                }
                $this->logger->info("creating response");
                $htmlTable .= '</table > ';
                $responseArray[] = array(
                    'html' => $htmlTable,
                    'percentile_price' => $this->percentilePrice,
                    'average_erf' => $this->averageErf,
                    'average_price' => $this->averagePrice,
                    'percentile_count' => $this->percentileCount
                );
                $this->logger->info("response array before return " . print_r($responseArray, true));

                return $responseArray;
            } else {
                $this->logger->info("properties not found");

                return "";
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
            return $responseArray;
        }
    }

    public function getFlipableProperties($type, $percentageCheaper, $bedrooms, $bathrooms, $erf): array|string
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        $propertiesArray = array();
        try {
            //get locations from table
            if (strcmp($type, 'average') === 0) {
                $properties = $this->getPropertiesPricedLessThanAVGNoLocation($percentageCheaper, $bedrooms, $bathrooms, $erf);
            }

            $htmlTable = '<table><tr>
<th>Location</th>
<th>#Properties</th>
<th>AVG Price</th>
<th>Price</th>
<th>AVG ERF</th>
    
    <th>ERF &#8593</th>
    <th>Bedrooms</th>
    <th>Bathrooms</th>
    <th>Parking</th>
    <th>Link</th>
  </tr>';

            $this->logger->info("before checking property count");
            if (count($properties) > 0) {
                $this->logger->info("count is greater than zero");
                foreach ($properties as $property) {
                    $locationAverages = $this->getLocationAvgERFAndPrice($property->getLocation(), $bedrooms, $bathrooms);
                    $averagePrice = "";
                    $averageErf = "";
                    $numberOfProperties = "";
                    foreach ($locationAverages as $locationAverage) {
                        $averagePrice = $locationAverage['price'];
                        $averageErf = $locationAverage['erf'];
                        $numberOfProperties = $locationAverage['count'];
                    }

                    $propertiesArray[] = array(
                        'location' => $property->getLocation(),
                        'price' => $property->getPrice(),
                        'erf' => $property->getErf(),
                        'bedrooms' => $property->getBedrooms(),
                        'bathrooms' => $property->getBathrooms(),
                        'parking' => $property->getGarage(),
                        'url' => $property->getUrl(),
                        'avg_price' => $averagePrice,
                        'avg_erf' => $averageErf,
                        'count' => $numberOfProperties
                    );

                    $sort = array();
                    foreach ($propertiesArray as $k => $v) {
                        $sort['price'][$k] = $v['price'];
                        $sort['erf'][$k] = $v['erf'];
                    }
                    # sort by event_type desc and then title asc
                    array_multisort($sort['erf'], SORT_DESC, $sort['price'], SORT_ASC, $propertiesArray);

                }

                $this->logger->info("looping propertiesArray");

                foreach ($propertiesArray as $property) {
                    $htmlTable .= '
                      <tr>
                      <td><a target="_blank" href="https://www.google.com/maps/place/Gauteng ' . $property['location'] . '">' . $property['location'] . '</a></td>
                      <td>' . $property['count'] . '</td>
                      <td>R' . number_format((float)$property['avg_price'], 0, '.', ' ') . '</td>
                      <td>R' . number_format((float)$property['price'], 0, '.', ' ') . '</td>
                     <td>' . number_format((float)$property['avg_erf'], 0, '.', '') . '</td>
                      
                        <td>' . $property['erf'] . '</td>
                        <td>' . $property['bedrooms'] . '</td>
                        <td>' . $property['bathrooms'] . '</td>
                        <td>' . $property['parking'] . '</td>
                        <td><a href="' . $property['url'] . '" target="_blank">Link</a></td>';
                }
                $this->logger->info("creating response");
                $htmlTable .= '</table > ';
                $responseArray[] = array(
                    'html' => $htmlTable,
                    'percentile_price' => $this->percentilePrice,
                    'average_erf' => $this->averageErf,
                    'average_price' => $this->averagePrice,
                    'percentile_count' => $this->percentileCount
                );
                $this->logger->info("response array before return " . print_r($responseArray, true));

                return $responseArray;
            } else {
                $this->logger->info("properties not found");

                return "";
            }

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
            return $responseArray;
        }
    }

    public function getPropertiesPricedLessThanAVGNoLocation($percentageCheaper, $bedrooms, $bathrooms, $erf): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $pricePercentage = 1 - floatval($percentageCheaper);
            $erfPercentage = 1 - floatval($erf);
            $query = $this->em->createQuery("SELECT p
            FROM App\Entity\FlipabilityProperty p
            where p.bedrooms > " . $bedrooms . "
            and p.bathrooms > " . $bathrooms . "
            and p.erf > (SELECT avg(p2.erf)*$erfPercentage FROM App\Entity\FlipabilityProperty p2 where p2.location = p.location)
            and p.price < (SELECT avg(p3.price)*$pricePercentage FROM App\Entity\FlipabilityProperty p3 where p3.location = p.location)");

            $this->logger->debug("SQL is:" . $query->getSQL());

            return $query->getResult();

        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
            return $responseArray;
        }
    }


    public function getLocationAvgERFAndPrice($location, $bedrooms, $bathrooms): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $query = $this->em->createQuery("SELECT p, avg(p.price) as price, avg(p.erf) as erf, count(p.id) as count
            FROM App\Entity\FlipabilityProperty p
            where  p.location = '" . $location . "' 
            and p.bedrooms > " . $bedrooms . "
             and p.bathrooms > " . $bathrooms . "
            GROUP BY p.location");
            return $query->getResult();
        } catch (Exception $ex) {
            $responseArray[] = array(
                'result_message' => $ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString(),
                'result_code' => 1
            );
            $this->logger->error("Error " . print_r($responseArray, true));
        }

        return $responseArray;
    }
}