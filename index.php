<?php
/*****************************************
                                       _ 
                                      | |
  ___  ___ _ __ __ _ _ __  _ __  _   _| |
 / __|/ __| '__/ _` | '_ \| '_ \| | | | |
 \__ \ (__| | | (_| | |_) | |_) | |_| |_|
 |___/\___|_|  \__,_| .__/| .__/ \__, (_)
                    | |   | |     __/ |  
                    |_|   |_|    |___/  
					
*****************************************/
$GLOBALS['Name'] = 'Scrappy';
$GLOBALS['Ver'] = '1.0';
/* Load Config.php if it exists */
if (file_exists('./config.php')) require('./config.php');

/* Shutdown function, should only print anything if we have an error. */
function shutdown(){
	$e=error_get_last(); 
	if($e!==null) {
		if (isset($_REQUEST['type']) && $_REQUEST['type']=='json') {
			echo json_encode(array('success'=>false,'err'=>$a['message']));
			exit;
		} else {
			echo $e['message'];
			exit;
		}
		exit;
	}
	exit;
} 
register_shutdown_function('shutdown'); // bind the shutdown function
set_time_limit(5); // run shutdown function if php runs for over 5 seconds.

/* Show all Errors */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *"); // Allow all CORS requests to Scrappy

$GLOBALS['err'] = false; // Global Error Variable, helps us catch the filesize and relay the message.

if (isset($_REQUEST['url']) && strlen($_REQUEST['url'])!==0) {
	
	/* The URL Parameter is found! Let's scrape it. */
	
	/* Initialize cURL */
	$curl = curl_init();
	// cURL Options
	curl_setopt($curl, CURLOPT_URL, $_REQUEST['url']); // URL To Fetch
	curl_setopt_array($curl,array(
		CURLOPT_FOLLOWLOCATION => true, // follow redirects
		CURLOPT_RETURNTRANSFER => 1, // don't immediately print
		CURLOPT_HEADER => 1, // Print Header
		CURLOPT_CONNECTTIMEOUT => 30, // Maximum connection time
		CURLOPT_BUFFERSIZE => 128, // more progress info
		CURLOPT_NOPROGRESS => false // Show Progress
	));
	curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, function($DownloadSize, $Downloaded, $UploadSize, $Uploaded){
		// Function to check the size of what we're downloading.
		if ($Downloaded > 2097152 /*2MB to bytes*/){
			$GLOBALS['err'] = 'Exceeded 2MB'; // Define error message from earlier.
			return 1; // Abort cURL.
		} else {
			return 0; // Resume fetch.
		}
	});
	if (isset($_REQUEST['r']) && strlen($_REQUEST['r'])!==0) curl_setopt($curl,CURLOPT_REFERER,$_REQUEST['r']);
	// User Agent
	if (isset($_REQUEST['ua']) && strlen($_REQUEST['ua'])!==0) {
		// Parameter-Defined User-Agent
		curl_setopt($curl,CURLOPT_USERAGENT,$_REQUEST['ua']);
	} else if (strlen($GLOBALS['UA'])!==0){
		// Config-Defined User-Agent
		curl_setopt($curl,CURLOPT_USERAGENT,$GLOBALS['ua']);
	} else {
		// Let's generate the User-Agent based on the Config/defaults.
		$ua = 'Mozilla/5.0 (compatible; ';
		$ua .= $GLOBALS['Name'].'/'.$GLOBALS['Ver'].';)';
		if (strlen($GLOBALS['Domain'])!==0) preg_replace('/\)/',$GLOBALS['Domain'].')',$ua);
		curl_setopt($curl,CURLOPT_USERAGENT,$ua);
	}
	
	$Page = curl_exec($curl); // Execute cURL request
	$curl_resp = curl_getinfo($curl); // Get the cURL response info into a seperate associative array.
	if (curl_errno($curl)){
		// cURL ran into an error, let's handle it.
		if ($GLOBALS['err']===false){
			// If our Global variable hasn't already been defined, use the cURL error to declare it.
			$GLOBALS['err'] = curl_error($curl); 
		}
	} else {
		// The fetch was successful! Let's split it up now.
		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE); // Get size of headers
		curl_close($curl); // terminate the cURL process, it's no longer needed.
		$headers = substr($Page, 0, $header_size); // Extract the headers from the response.
		$body = substr($Page, $header_size); // Extract the HTML body from the response.
	}
	
	// Now let's print our response.
	if (isset($_REQUEST['type']) && $_REQUEST['type']=='json'){
		
		// Requested type is JSON, so let's set the MIME type
		header("Content-Type:application/json; charset=UTF-8");
		
		$JSON = array(); // Declare new Array.
		if ($GLOBALS['err']!==false){
			// There was an error! We need to print that out then.
			$JSON += ['success'=>false];
			$JSON += ['err'=>$GLOBALS['err']];
		} else {
			// No Errors!
			$JSON += ['success'=>true];
			$JSON += ['status'=>$curl_resp]; // Copy our cURL response from before.
			$JSON += ['content'=>utf8_encode($body)]; // Copy our body as a string.
		}
		// Let's convert it to a JSON! (Easy as pie)
		echo json_encode($JSON,JSON_FORCE_OBJECT); 
		exit; // Mission accomplished!

	} else if (isset($_REQUEST['type']) && $_REQUEST['type']=='plain'){
		header("Content-Type:text/plain; charset=UTF-8");
		if (isset($_REQUEST['resp']) && $_REQUEST['resp']=='true') {
			echo $headers;
		} else {
			echo $body;
		}
		exit; // Mission accomplished!
	} else {
		if (isset($_REQUEST['resp']) && $_REQUEST['resp']=='true') {
			echo '<style>table{width:100%;} table, th, td {border: 1px solid black;border-collapse: collapse;padding:5px;} td.string{color:DarkGoldenRod;} td.integer{color:DarkBlue ;}  td.double{color:Tomato;} td:nth-child(2){font-weight: bold;}</style>';
			echo '<table><tr><th>Variable</th><th>Value</th></tr>';
			foreach ($curl_resp as $key => $value) {
				echo '<tr><td>'.$key.'</td>';
				$valtype = gettype($value);
				switch($valtype){
					case 'array':
					$val2 = implode('',$value);
					break;
					
					default:
					$val2 = $value;
					break;
					
				}
				echo '<td class="'.$valtype.'">'.$val2.'</td></tr>';
			}
			echo '</table>';
		} else {
			header('Content-Type: '.$curl_resp['content_type']);
			echo $body;
		}
	}
} else if ($_SERVER['REQUEST_METHOD']==='GET') {
    echo file_get_contents('./default.html');
	exit;
} else {
	if (isset($_REQUEST['type']) && $_REQUEST['type']=='json') {
		echo json_encode(array('success'=>false,'err'=>'Missing parameter "url"'));
		exit;
	} else {
		echo 'Missing parameter "url"';
		exit;
	}
}
?>