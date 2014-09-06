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
    <Message>$sid</Message>
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

// govready.io/io - Log any install (e.g. download) of govready
//-------------------------------------------------------------
$app->get('/io/', function () use ($app) {

	// prepare log entry
	date_default_timezone_set('UTC');
	$requested    = date(DATE_RFC2822, $_SERVER['REQUEST_TIME']); 
	$ip_address   = $_SERVER['REMOTE_ADDR'];
	$user_agent   = $_SERVER['HTTP_USER_AGENT'];
	$event        = "install";
	$target       = "https://raw.githubusercontent.com/GovReady/govready/master/install.sh";
	$domain_name  = gethostbyaddr($ip_address);

	$entry = "$requested\t$ip_address\t$domain_name\t$user_agent\t$event\t$target\n";

	// log entry 
	// /var/local/govreadystats/govreadystats.log
	file_put_contents("/var/local/govreadystats/govreadystats.log", $entry, FILE_APPEND | LOCK_EX);

	// Prepare a simple response
	$response = array('status' => "OK", 'message' => "io.govready.org/io", 'entry' => $entry);

	// Send json header
	header('Content-Type: application/json');

	// Send json encoded reponse
	echo json_encode($response);

	// send text to Greg
	// set your AccountSid and AuthToken from www.twilio.com/user/account
	$AccountSid = ACCOUNTSID;
	$AuthToken = AUTHTOKEN;
	$client = new Services_Twilio($AccountSid, $AuthToken);
	$msg =  substr("GovReady $event.\n $ip_address, $domain_name, $user_agent", 0, 140);
    $sms = $client->account->sms_messages->create(
    	"860-245-2269", // From this number
    	"917-304-3488", // To this number
    	$msg
	);
	$sid = $sms->sid;
	
});

// govready.io/install - Redirect to Github.com/GovReady/govready repo install 
//-----------------------------------------------------------------------------
$app->get('/install', function () use ($app) {

	// Redirect url to get content from govready repo on github
	$app->response()->status(302);
	$app->response()->header('Location', 'https://raw.githubusercontent.com/GovReady/govready/master/install.sh');

});

$app->run();

?>

