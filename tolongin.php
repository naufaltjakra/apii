<?php

//echo getHostByName(php_uname('n'));
//echo '--'.$_SERVER['REMOTE_ADDR'];
// 9a79e199e9fb9ee4951cac05ca01d4ef
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

	$kd_mintol=$_POST['kd_mintol'];
	$status=$_POST['status'];
	$create_at=$_POST['create_at'];

	$curl = curl_init();

	$url="http://tolongin.96.lt/v1/tolongin";

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,//10
	  CURLOPT_TIMEOUT => 30,//30
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "kd_mintol=".$kd_mintol."&status=".$status."&create_at=".$create_at,
	  CURLOPT_HTTPHEADER => array(
		"content-type: application/x-www-form-urlencoded"
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
		//
	}

?>
