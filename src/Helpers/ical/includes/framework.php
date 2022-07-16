<?php
/**
 * framework.php - framework file
 *
 * @package	ZapCalLib
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2017 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link	http://icalendar.org/php-library.html
 */

// No direct access

/**
 * set MAXYEAR to 2036 for 32 bit systems, can be higher for 64 bit systems
 *
 * @var integer
 */
define('_ZAPCAL_MAXYEAR', 2036);

/**
 * set MAXREVENTS to maximum # of repeating events 
 *
 * @var integer
 */
define('_ZAPCAL_MAXREVENTS', 5000);

require_once('/date.php');
require_once('/recurringdate.php');
require_once('/ical.php');
require_once('/timezone.php');
