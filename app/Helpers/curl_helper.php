<?php

if ( ! function_exists( 'simpleCurl' ) )
{
	function simpleCurl() {

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://localhost/codeigniter/bapi/extension/crud",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "author=administrator&contact=administrator@local.host&category_name=unknown&description=unknown&name=Forum%20extension&slug=Forum%20&version=0.1&events%5B0%5D%5Bmethod%5D=index&events%5B0%5D%5Bname%5D=forum-event&events%5B1%5D%5Bmethod%5D=getMap&events%5B1%5D%5Bname%5D=forum-map&hashed_file=%242y%2410%246IvB%5C/FrKiUfaIUwL94XX6O3Uno%5C/dXeYb6jk%5C/twsUM9.IrctDeXGXy",
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded"
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		echo $response;
	}
}