<?php

//echo getHostByName(php_uname('n'));
//echo '--'.$_SERVER['REMOTE_ADDR'];

// include('functions.php');

	$curl = curl_init();

	$nim=$_POST['nim'];
	$password=$_POST['password'];

	$url="http://localhost/kuliah/ppl/api/v1/login";

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,//10
	  CURLOPT_TIMEOUT => 30,//30
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "nim=".$nim."&password=".$password,
	  CURLOPT_HTTPHEADER => array(
		"content-type: application/x-www-form-urlencoded"
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

?>
