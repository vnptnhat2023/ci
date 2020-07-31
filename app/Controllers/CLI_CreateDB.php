<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class CLI_CreateDB extends Controller
{
	public function index()
	{
		if ( false === is_cli() ) { exit( 0 ); }

		$serverName = env( 'database.default.hostname' );
		$username = env( 'database.default.username' );
		$password = env( 'database.default.password' );
		$database = env( 'database.default.database' );

		// print_r( [ $serverName, $username, $password, $database ] ); die;

		// Create connection
		$conn = mysqli_connect( $serverName, $username, $password );

		// Check connection
		if ( ! $conn ) { die( 'Connection failed: ' . mysqli_connect_error() ); }

		if ( null === $database ) { die( 'Database not defined.' ); }

		// Create database
		$sql = "CREATE DATABASE IF NOT EXISTS {$database}";

		if ( mysqli_query( $conn, $sql ) )
		{
			echo "Database: [ {$database} ] created successfully";
		}
		else
		{
			echo "Error creating database: " . mysqli_error( $conn );
		}

		mysqli_close( $conn );
	}
}