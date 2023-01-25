<?php
require_once (__DIR__ . '/../utils/data.php');
require_once (__DIR__ . '/../app/application.php');

getTodayEmails();

function getTodayEmails()
{
    $mailConn = imap_open("{".EMAIL_SERVER.":".EMAIL_SERVER_PORT."/pop3/ssl/novalidate-cert}", EMAIL_ADDRESS, EMAIL_PASSWORD);

    $search = 'ON ' . date('d-M-Y'); // search for today's email only
    $emails = imap_search($mailConn, $search);

    if ($emails) {
        foreach ($emails as $emailID) {
            $overview = imap_fetch_overview($mailConn, $emailID, 0);
            $emailSubject = $overview[0]->subject;
            try {
                $pos = strpos($emailSubject, 'Reservation confirmed');
                if ($pos !== false) {

                    $emailMsgNumber = $overview[0]->msgno;

                    $bodyText = imap_fetchbody($mailConn, $emailMsgNumber, 1);
                    if (! strlen($bodyText) > 0) {
                        echo 'body is empty';
                        $bodyText = imap_fetchbody($mailConn, $emailMsgNumber, 1);
                    }

                    $bodyText = quoted_printable_decode($bodyText);

                    $guestName = getStringByBoundary($emailSubject, 'Reservation confirmed - ', ' arrives ');
                    $reservationId = getStringByBoundary($bodyText, 'Confirmation code', 'View itinerary');
                    
                    updateGuestName($reservationId, $guestName);
                    
                }
            } catch (\Throwable $e) {
                $temparray1 = array(
                    'result_code' => 1,
                    'result_desciption' => "Exception occured"
                );

                echo json_encode($temparray1);
                print_r($e);
            }
        }
    } else {
        $temparray1 = array(
            'result_code' => 0,
            'result_desciption' => "no emails found"
        );
        
        echo json_encode($temparray1);
    }
}

function getStringByBoundary($string, $leftBoundary, $rightBoundary){
    preg_match('~'.$leftBoundary.'([^{]*)'.$rightBoundary.'~i', $string, $match);
    var_dump($match[1]); // string(9) "123456789"
    return $match[1];
}

function updateGuestName($airbnbReservationID, $guestName)
{
    $sql = 'UPDATE wpky_hb_resa set admin_comment = "Name: ' . trim($guestName) . '" WHERE `origin_url` like "%' . trim($airbnbReservationID) . '%"';
    echo $sql;

    $resultCreateRes = updaterecord($sql);
    if (strcasecmp($resultCreateRes, "Record updated successfully") == 0) {
        $temparray1 = array(
            'result_code' => 0,
            'result_desciption' => "Airbnb Guest name successfully updated"
        );

        echo json_encode($temparray1);
    }
}
