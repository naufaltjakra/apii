<?php
	header('Content-Type: application/json');
	require_once('functions.php');

	// Mengirim data mahasiswa sesuai NIM
	$curl = curl_init();

	$nim=test_input($conn,$_GET['nim']);
	$dataMhs=array();

	// Cek ApiKey
	if(isValid($conn,$apiKey)){
		// echo "ApiKey valid";
		// Ambil Data Mahasiswa
		$query=mysqli_query($conn, "SELECT * FROM user WHERE nim='".$nim."'");

		$dataMhs['query']=array('nim'=>$nim);

		if($query){
			$dataMhs['message']='Berhasil mengambil data.';
			$dataMhs['error']=false;
		}else{
			$dataMhs['message']='Gagal mengambil data.';
			$dataMhs['error']=true;
		}
		$dataMhs['results']=mysqli_fetch_assoc($query);
		// var_dump($dataMhs);

	}else{
		$dataMhs['message']='ApiKey tidak valid';
		$dataMhs['error']=true;
	}

	$response=json_encode($dataMhs);
	echo $response;
	/*
	if ($err) {
		echo "cURL Error #:" . $err;
	} else {
		//;
	}*/
?>
