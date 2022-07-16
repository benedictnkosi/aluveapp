<?php
/**
 * simpleevent.php
 *
 * @package	ZapCalLib
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2017 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link	http://icalendar.org/php-library.html
 */

/**
 * Simple Event Example
 *
 * Create a simple iCalendar event
 * No time zone specified, so this event will be in UTC time zone
 *
 */

require_once (__DIR__ . '/../reservations/commons.php');
require_once ( __DIR__ . '/../utils/data.php');
require_once("zapcallib.php");

    
importIcal($_GET["accom_id"], "https://sync.guestyforhosts.com/d735b5bc-60dd-471f-9b24-dc43c2a9c756/8ae0b332-0cc6-4459-82fc-c74719ae7188");
//createIcal($_GET["accom_id"]);


function createIcal($accomodationId){
    $sql = "SELECT reservations.id, guest.id as guest_id, room_id, paid, reservations.price, rooms.name as room_name, reservations.status, additional_info, origin, check_in, check_out,  origin_url, received_on,guest_id, id_image, phone_number, guest.name as guest_name, email  

FROM `reservations`, `guest`, rooms WHERE

`reservations`.`guest_id` = `guest`.`id`

and rooms.ID = `reservations`.room_id

and rooms.ID = " . $accomodationId . " and (reservations.status = 'confirmed' or (reservations.status = 'pending' and paid NOT IN ('0.00')) or (reservations.status = 'pending' and origin NOT IN ('website')))

and DATE(check_in) >= DATE(NOW()) - INTERVAL 1 DAY

order by `check_in`;";
    
    
    $results = querydatabase($sql);
    $rsType = gettype($results);
    
    if (strcasecmp($rsType, "string") == 0) {
        echo "No events";
        exit();
    }

    
    // create the ical object
    $icalobj = new ZCiCal("-//Aluve Guesthouse//". $accomodationId . "// EN");
    $roomname = "";
    while ($result = $results->fetch_assoc()) {
        $resId = $result["id"];
        $event_start = $result["check_in"] . "00:01:00";
        $event_end = $result["check_out"] . "23:59:00";
        
        $guestName = $result["guest_name"];
        $guestEmail = $result["email"];
        $roomname = $result["room_name"];
        
        $title = "Aluve - " . $guestName . "  - Resa id: ".$resId;
        // date/time is in SQL datetime format

        // create the event within the ical object
        $eventobj = new ZCiCalNode("VEVENT", $icalobj->curnode);
        
        // add title
        $eventobj->addNode(new ZCiCalDataNode("SUMMARY:" . $title));
        
        // add start date
        $eventobj->addNode(new ZCiCalDataNode("DTSTART:" . ZCiCal::fromSqlDateTime($event_start)));
        
        // add end date
        $eventobj->addNode(new ZCiCalDataNode("DTEND:" . ZCiCal::fromSqlDateTime($event_end)));
        
        // UID is a required item in VEVENT, create unique string for this event
        // Adding your domain to the end is a good way of creating uniqueness
        $uid = date('Y-m-d-H-i-s') . "@demo.icalendar.org";
        $eventobj->addNode(new ZCiCalDataNode("UID:" . $uid));
        
        // DTSTAMP is a required item in VEVENT
        $eventobj->addNode(new ZCiCalDataNode("DTSTAMP:" . ZCiCal::fromSqlDateTime()));
        
        // Add description
        $eventobj->addNode(new ZCiCalDataNode("Description:" . ZCiCal::formatContent(
            "NAME: ".$guestName." \nEMAIL: ".$guestEmail)));
        
    }
    
    // write iCalendar feed to stdout
    $icalString =  $icalobj->export();
    header('Content-type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename=aluve' . strtolower(str_replace(" ","-",$roomname)) .'.ics');
    echo $icalString;
    exit;
}

function importIcal($accomodationId, $url){
    
    //read events
    $events = iCalDecoder($url);
    
    //print_r($events);
        
        foreach($events as $event){
            $now = new DateTime();
            
            $today = date("d.m.Y");
            $yesterday  = new DateTime($today);
            $interval = new DateInterval('P1D');
            $yesterday->sub($interval); 
            
            $date_event = new DateTime($event['DTSTART;VALUE=DATE']);
            $startdate = date('Y-m-d', strtotime($event['DTSTART;VALUE=DATE']));//user friendly date
            
            if ($yesterday > $date_event) {
                echo $date_event->format('Y-m-d H:i:s')  . " great in the past <br>";
                continue;
            }
            
            
            $enddate = date('Y-m-d', strtotime($event['DTEND;VALUE=DATE']));//user friendly date
            $summary= $event['SUMMARY'];
            $description = $event['DESCRIPTION'];
            $status;
            $created;
            $guestName;
            $guestPhoneNumber = "";
            $uid = $event['UID'];
            $email;
            
            //Guesty
            if (str_contains($url, 'guestyforhosts.com')) {
                $status = "Confirmed";
                $created = $event['DTSTAMP'];
                
                $pieces = explode("-", $summary);
                $guestName = $pieces[1];
                $pieces = explode(":", $description);
                $guestPhoneNumber = $pieces[2];
                $pos = strpos($guestPhoneNumber, "Email");
                $guestPhoneNumber = trim(substr($guestPhoneNumber, 0,  $pos));
                $email = trim(str_replace("ATTENDEE", "", $pieces[3]));
            }
            //airbnb
            
            //booking.com
            
            echo $uid . " - " . $guestPhoneNumber . "<br>";
            //check if booking already imported
            $sql = "Select * from reservations where uid = '" .$uid. "';";
            $results = querydatabase($sql);
            $rsType = gettype($results);
            
            //if booking not imported
            if (strcasecmp($rsType, "string") == 0) {
                $guestID = createCustomer($guestName, $guestPhoneNumber, $email);
                
                $sqlCreateinvoice = "INSERT INTO `reservations` (`check_in`, `check_out`, `room_id`, `price`,  `paid`,  `guest_id`,reservations.status, `additional_info`,  `received_on`, `updated_on`, `uid`, `origin`, `origin_url`,`check_in_status`,`cleanliness_score`)
VALUES
('" . $startdate . "', '" . $enddate . "', " . $accomodationId . ", '0', '0'," . $guestID . ", '" . $status . "', '', '" . $now->format('Y-m-d H:i:s') . "', '" . $now->format('Y-m-d H:i:s') . "', '" . $uid . "', 'guestyforhosts.com','guestyforhosts.com', 'not_checked_in', 0)";
                
                //echo $sqlCreateinvoice;
                $resultCreateRes = insertrecord($sqlCreateinvoice);
                if (strcasecmp($resultCreateRes, "New record created successfully") != 0) {
                    echo "failed to import booking";
                }
            }
            
        }
}
    


function iCalDecoder($file) {
    $ical = file_get_contents($file);
    preg_match_all('/(BEGIN:VEVENT.*?END:VEVENT)/si', $ical, $result, PREG_PATTERN_ORDER);
    for ($i = 0; $i < count($result[0]); $i++) {
        $tmpbyline = explode("\r\n", $result[0][$i]);
        
        foreach ($tmpbyline as $item) {
            $tmpholderarray = explode(":",$item);
            if (count($tmpholderarray) >1) {
                $majorarray[$tmpholderarray[0]] = $tmpholderarray[1];
            }
        }
        
        if (preg_match('/DESCRIPTION:(.*)END:VEVENT/si', $result[0][$i], $regs)) {
            $majorarray['DESCRIPTION'] = str_replace("  ", " ", str_replace("\r\n", "", $regs[1]));
        }
        $icalarray[] = $majorarray;
        unset($majorarray);
        
    }
    return $icalarray;
}



    
    
    
