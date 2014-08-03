<?php

/*
 * file: functions.inc.php
 * version: 0.0.1
 */


//
// Parse simple log
// Write server request to log at Parse
// Benefit: Storing record of requests in online database
//
// parse_simple_log
// ----------------------------------------------------------
function parse_simple_log($parse, $phone, $message) {
	echo "phone: $phone; message: $message";
	$result = "phone: $phone; message: $message";

	// Create parameters
	$user = "GregElin";
	$ts = time();
	$tsh = date('Y-m-d', $ts);

	$params = array(
	    'className' => 'ParseLogTest',
	    'object' => array(
	        'user'		=> $user,
	        'phone'		=> $phone,
	        'message'	=> $message,
	        'date'      => $tsh
	    )
	);

	$request = $parse->create($params);
	return $request;
}

//
// log_to_parse
// ------------------------------------------------------------
function log_to_parse($event, $identifier, $identifier_class, $summary) {
	// Create parameters
	$ts = time();
	$tsh = date('Y-m-d', $ts);

	// Build params array
	$params = array(
	    'className' => 'CloudStartLog',
	    'object' => array(
	        'event'				=> $event,
	        'identifier'		=> $identifier,
	        'identifier_class'	=> $identifier_class,
	        'summary'			=> $summary,
	        'date'      		=> $tsh
	    )
	);

	// Instantiate parse object
	$parse = new parseRestClient(array(
    	'appid' => PARSE_APPLICATION_ID,
    	'restkey' => PARSE_API_KEY
	));

	// Send data to parse
	$request = $parse->create($params);
	return $request;
}

?>