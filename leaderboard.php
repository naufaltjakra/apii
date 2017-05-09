<?php

//echo getHostByName(php_uname('n'));
//echo '--'.$_SERVER['REMOTE_ADDR'];

// include('functions.php');

	function getRequestHeaders() {
		$headers = array();
		foreach($_SERVER as $key => $value) {
			if (substr($key, 0, 5) <> 'HTTP_') {
				continue;
			}
			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			
			$headers[$header] = $value;
		}
		return $headers;
	}

	$headers = getRequestHeaders();

	foreach ($headers as $header => $value) {
		if($header=="Authorization"){
			$apiKey = $value;
			break;
		}
		//echo "$header: $value <br />\n";
	}

// if(isset($_GET['username'])){
	// // Nampilin detail permintaan
	// $curl = curl_init();

	// $username=$_GET['username'];
	
	// $url="http://tolongin.96.lt/v1/leaderboard/".$username;

	// curl_setopt_array($curl, array(
	  // CURLOPT_URL => $url,
	  // CURLOPT_RETURNTRANSFER => true,
	  // CURLOPT_ENCODING => "",
	  // CURLOPT_MAXREDIRS => 10,//10
	  // CURLOPT_TIMEOUT => 30,//30
	  // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  // CURLOPT_CUSTOMREQUEST => "GET",
	  // CURLOPT_HTTPHEADER => array(
		// "content-type: application/x-www-form-urlencoded",
		// "Authorization: ".$apiKey.""
	  // ),
	// ));

	// $response = curl_exec($curl);
	// $err = curl_error($curl);
	// print_r($response);
	// curl_close($curl);

	// $results = json_decode($response);
	  
	// if ($err) {
		// echo "cURL Error #:" . $err;
	// } else {
		// //;
	// }
// }else{
	// Nampilin daftar leaderboard	
	$curl = curl_init();
	
	$url="http://tolongin.96.lt/v1/leaderboard";

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,//10
	  CURLOPT_TIMEOUT => 30,//30
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_POSTFIELDS => "",
	  CURLOPT_HTTPHEADER => array(
		"content-type: application/x-www-form-urlencoded",
		"Authorization: ".$apiKey.""
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	print_r($response);
	curl_close($curl);

	$results = json_decode($response);
	  
	if ($err) {
		echo "cURL Error #:" . $err;
	} else {
		//;
	}
			  
	
//}
?>