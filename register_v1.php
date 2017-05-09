<?php

$curl = curl_init();

$username=$_POST['username'];
$nim=$_POST['nim'];
$password=$_POST['password'];

$url="http://pplasik.96.lt/v1/register";

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,//10
  CURLOPT_TIMEOUT => 30,//30
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "username=".$username."&password=".$password."&nim=".$nim,
  CURLOPT_HTTPHEADER => array(
  "content-type: application/x-www-form-urlencoded"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
print_r($response);
curl_close($curl);

 ?>
