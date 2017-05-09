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

if(isset($_GET['id'])){
	// Nampilin detail permintaan
	$curl = curl_init();

	$id=$_GET['id'];
	
	$url="http://tolongin.96.lt/v1/timeline/".$id;

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
}elseif(isset($_POST['judul'])){
	// Tambah Permintaan Pertolongan
	$judul=$_POST['judul'];
	$deskripsi=$_POST['deskripsi'];
	$foto=$_POST['foto'];
	
	$unique_id=date("d-m-y")."_".uniqid().".jpg";
	
	$path="assets/images/timeline/".$unique_id;
			
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
	$lokasi=$_POST['lokasi'];
	$kategori=$_POST['kategori'];
	
	$curl = curl_init();
	
	$url="http://tolongin.96.lt/v1/timeline";

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,//10
	  CURLOPT_TIMEOUT => 30,//30
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "judul=".$judul."&deskripsi=".$deskripsi."&foto=".$foto."&lokasi=".$lokasi."&kategori=".$kategori,
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
	
}else{
	// Nampilin daftar permintaan	
	$curl = curl_init();
	
	$url="http://tolongin.96.lt/v1/timeline";

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
			  
	
}
?>