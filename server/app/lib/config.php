<?php

/**
 * Connection Base MySQL
 */


define('DSN_MY', 'mysql:host=localhost;dbname=user16');
define('USER_NAME', 'user16');
define('PASS', 'user16');
define('TIME_FORMAT', 'Y-m-d H:i:s');

/*
define('DSN_MY', 'mysql:host=localhost;dbname=user16');
define('USER_NAME', 'root');
define('PASS', '');
define('TIME_FORMAT', 'Y-m-d H:i:s');
*/
/**
 * Errors
 */
define('ERR_ACCESS', 'Access denied!');
define('ERR_ADDEVENT', 'Error you event intersects with some other event! Check the date or time!');
define('ERR_A_DEL', 'Error, admin should be necessarily!');
define('ERR_AUTH', 'Error, wrong password and login');
define('ERR_DATA', 'Error, Missing data!');
define('ERR_DB', 'Error connecting to DB');
define('ERR_FIELDS', 'Some fields are empty!');
define('ERR_QUERY', 'Error DB query');
define('ERR_LOGIN', 'This login or email exists');
define('ERR_SEARCH', 'Found nothing!');

/**
 * User errors
 */
define('INCORRECT_EMAIL', 'Invalid email format!');
define('INCORRECT_LOGIN', 'Wrong login - the login can consist only of letters of the English alphabet and numbers without spaces and must be at least 3 characters and not more than 30');
define('INCORRECT_PASS', 'Invalid password format!');
define('INCORRECT_RECURR', 'Wrong values for recurring event');
define('INCORRECT_TEXT', 'Description should be consist of 6 symbols at least!');
define('INCORRECT_TIME_FOR_EVENT', 'The time of the beginning of the event should be less and not exactly the end time!');
define('INCORRECT_TIME_S_E', 'Your event is out of the acceptable time!');
define('INCORRECT_USERNAME', 'Invalid User name - User Name must be at least 3 characters and not more than 35');
define('INCORRECT_WEEKEND', 'At the weekend the Boardroom is closed - pleace, choose another date!');

/**
 * Set time zone
 * Set default encoding
 * Save Weekend Day (0 - Sunday, 6 - Saturday)
 * Start and End time for events
 */
date_default_timezone_set('Europe/Kiev');
define('ENCODE_DEFAULT', '.json');
define('WEEKEND1', 0);
define('WEEKEND2', 6);
define('TIME_START', 8);
define('TIME_END', 20);

