<?php

// Configuration
//-------------------------------
// error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);
define("ERROR_LOG_FILE", "/tmp/php-error.log");
ini_set("error_log", ERROR_LOG_FILE);
date_default_timezone_set('America/New_York'); 

if (!file_exists('credentials.inc.php')) {
   echo "My credentials are missing!";
   exit;
}

// Include libraries added with composer
require 'vendor/autoload.php';
// Include credentials
require 'credentials.inc.php';
// Include application functions
require 'functions.inc.php';

 
// Functions
//-------------------------------
function TempLogger($message) {
	error_log( "Logger $message" );
}


// Start Slim router
//-------------------------------
$app = new \Slim\Slim();

// Router Methods
//-------------------------------

// cloudstart.io/ - displays placeholder landing page
//---------------------------------------------------
$app->get('/', function () use ($app) {

    $paramValue = $app->request->get('param');
    $response = <<<HTML
<html>
        <head>
                <title>GovReadyStats</title>
        </head>
        <body>
        <p>Welcome to GovReadyStats</p>
    </body>
</html>
HTML;

        return $app->response->setBody($response);
});

//
// Parameter example
//
// cloudstart.io/hello/:name - echos name back
//---------------------------------------------------
$app->get('/hello/:name', function ($name) use ($app) {
	$name = trim($name);
    echo "Hello, $name!";
    TempLogger( "Hello, $name!" );
});
//
// End paramer example
//

# cloudstart.io/error_log - prints error log to screen
$app->get('/error_log/', function () {
	TempLogger( "read log" );
	echo ERROR_LOG_FILE;
	echo "<pre>";readfile(ERROR_LOG_FILE);echo "</pre>";
});

//
// Redirect example - /bar receives from /foo
//
// cloudstart.io/foo - Redirects /foo to /bar
//-------------------------------------------
$app->get('/foo', function () use ($app) {

	$paramValue = $app->request->get('param');
    $app->redirect("/bar?param=$paramValue");

});

// cloudstart.io/bar - /bar receives from /foo
//---------------------------------------------
$app->get('/bar', function () use ($app) {
    
    $paramValue = $app->request->get('param');
    $response = <<<HTML
<html>
	<head>
		<title>GM bar</title>
	</head>
	<body>
    	<p>I am /bar</p>
    	<p>param = $paramValue</p>
    </body>
</html>
HTML;

	return $app->response->setBody($response);
});
//
// End Redirect example
//


//
// Twilio test
// Click this link to send test text message via Twilio. Tests message sending function
// Benefit: Simple test of sending message
//
// cloudstart.io/twilio/test - Send test text message via Twilio
//---------------------------------------------------------------
$app->get('/twilio/test', function () use ($app) {

// set your AccountSid and AuthToken from www.twilio.com/user/account
$AccountSid = ACCOUNTSID;
$AuthToken = AUTHTOKEN;
$client = new Services_Twilio($AccountSid, $AuthToken);
 
$sms = $client->account->sms_messages->create(
    "860-245-2269", // From this number
    "917-304-3488", // To this number
    "Test message from within slim!"
);
 
// Display a confirmation message on the screen
$sid = $sms->sid;
$response = <<<XML
<Response>
    <Message>$sid<Message>
</Response>
XML;

	$app->response->headers->set('Content-Type', 'text/xml');
	return $app->response->setBody($response);

});



//
// Error handling routes
//----------------------
//


//
// Other Routes
//

// cloudstart.io/status - Status report of system - HTML
//--------------------------------
$app->get('/status', function () use ($app) {
	// global $app;
	TempLogger( "status" );

	$response = <<<HTML
<html>
	<head>
		<title>GovReady Status</title>
	</head>
	<body>
    	<p>Status: OK</p>
    </body>
</html>
HTML;

	TempLogger("Response: Status: OK");
	return $app->response->setBody($response);
});


// cloudstart.io/status.xml - Status report of system - XML
//---------------------------------------------------------
$app->get('/status.xml', function () use ($app) {
	// global $app;
	TempLogger( "status.xml" );

	$response = <<<XML
<Response>
    <Message>Status: OK</Message>
</Response>
XML;

	$app->response->headers->set('Content-Type', 'text/xml');
	TempLogger("Response: Status: OK");
	return $app->response->setBody($response);
	
});

$app->run();

?>

