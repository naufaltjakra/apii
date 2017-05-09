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

if(isset($_GET['username'])){
	// Nampilin detail user
	$curl = curl_init();

	$username=$_GET['username'];
	
	$url="http://tolongin.96.lt/v1/user/".$username;

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,//10
	  CURLOPT_TIMEOUT => 30,//30
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
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
}else/*if(isset($_POST['username']))*/{
	// edit user
	$username=$_POST['username'];
	$nama=$_POST['nama'];
	$kategori=$_POST['kategori'];
	$email=$_POST['email'];
	$alamat=$_POST['alamat'];
	$no_telp=$_POST['no_telp'];
	$foto=$_POST['foto'];
	
	$unique_id=date("d-m-y")."_".uniqid().".jpg";
	
	$path="assets/images/user/".$unique_id;
			
	$imageData = base64_decode($foto);
	$source = imagecreatefromstring($imageData);
	$rotate = imagerotate($source, $angle, 0); // if want to rotate the image
	$imageSave = imagejpeg($rotate,$path,100);
	imagedestroy($source);
	
	// file_put_contents($path,$imageSave);
	if(!$imageSave){
		$response["error"] = true;
		$response["message"] = "Gambar gagal disimpan. Proses dibatalkan.";
		
		print_r(json_encode($response));
		exit;
	}
	
	
	$foto=$path;
	
	
	$curl = curl_init();
	
	$url="http://tolongin.96.lt/v1/user";

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,//10
	  CURLOPT_TIMEOUT => 30,//30
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "username=".$username."&nama=".$nama."&kategori=".$kategori."&email=".$email."&alamat=".$alamat."&no_telp=".$no_telp."&foto=".$foto,
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
	
}
?>