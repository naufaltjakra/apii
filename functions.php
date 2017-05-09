<?php
  ob_start();
  error_reporting(1);

	require_once('connect.php');

	$conn = mysqli_connect($host, $mysql_user, $mysql_pass, $db_name);
	if(mysqli_connect_errno()){
		die('Could not connect to database : <br/>'.$mysqli_connect_error());
	}

	$site_name="SISWA";
	session_start();
	date_default_timezone_set("Asia/Jakarta");

	function test_input($conn,$data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		$data = mysqli_real_escape_string($conn,$data);
		return $data;
	}

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

	$headers = apache_request_headers();//getRequestHeaders();
	// var_dump($headers);

	foreach ($headers as $header => $value) {
		// echo "$header: $value <br />\n";
		if($header=="Authorization"){
			$apiKey = $value;
			break;
		}

	}

	function isValid($conn,$apiKey){
		$query=mysqli_query($conn,"SELECT * FROM user WHERE api_key='".$apiKey."'");
		if(mysqli_num_rows($query)>0){
			return TRUE;
		}else{
			return FALSE;
		}

	}

   function getUserByUsername($nim) {
      // $query=mysqli_query($conn, "SELECT * FROM user WHERE nim='".$nim."'");
     $user = array();
      // $row = $query->fetch_object();
      $query = "SELECT * FROM user WHERE nim='".$nim."'";
      $result = $conn->query( $query );
      while($row = $result->fetch_object()){
        $user["username"] = $row->username;
        $user["nim"] = $row->nim;
        $user["api_key"] = $row->api_key;
        $user["created_at"] = $row->created_at;
        $user["ipk"] = $row->ipk;
        $user["pembimbing"] = $row->pembimbing;
        $user["judul_ta"] = $row->judul_ta;
    	}mysqli_close($conn);
      // if ($query) {
      //     // $user = $stmt->get_result()->fetch_assoc();
      //     // $stmt->bind_result($username, $nim, $api_key, $created_at, $ipk, $pembimbing, $judul_ta);
      //     // $stmt->fetch();
      //     $user = array();
      //     $user["username"] = $row->username;
      //     $user["nim"] = $row->nim;
      //     $user["api_key"] = $row->api_key;
      //     $user["created_at"] = $row->created_at;
      //     $user["ipk"] = $row->ipk;
      //     $user["pembimbing"] = $row->pembimbing;
      //     $user["judul_ta"] = $row->judul_ta;
      //     // $stmt->close();
      //     mysqli_close($conn);
      //     return $user;
      // } else {
      //     return NULL;
      // }
  }
	/*
	$nama_session_login='masuk_adim'; // username
	$level_session_login='level_adim'; // level
	*/

	/*
	if(isset($_SESSION[$nama_session_login])){
		$username=$_SESSION[$nama_session_login];
		$level=$_SESSION[$level_session_login];
	}

	function enkripsi_password($password){
		return md5("bina_".$password."_amal");
	}
	*/
?>
