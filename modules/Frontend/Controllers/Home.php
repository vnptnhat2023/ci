<?php 
namespace FAPI\Controllers;

class Home extends BaseController {

	public function index()
	{
		#HELPER
		// helper('FAPI');
		// echo FAPI_First() . '<br>';

		#CONFIG
		// $assetConfig = config('Assets');		
		// echo array_print($assetConfig);

		#LIBRARY
		// $FAPI_Lib = new \FAPI\Libraries\FAPI;
		// var_dump($FAPI_Lib);

		#MODE
		// $FABI_Model = model('\FAPI\Models\FAPI_Model');
		// $FABI_Model = new \FAPI\Models\FAPI_Model('test');
		// var_dump($FABI_Model);

		#View
		echo view('\FAPI\Views\FAPI_View', [ 'title' => 'FAPI VIEW TITLE' ]);
	}

}

/* End of file Home.php */
/* Location: ./frontend/controllers/Home.php */