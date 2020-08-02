<?php

namespace FAPI\Controllers;

class Home extends BaseController {

	public function index()
	{
		return view('\FAPI\Views\FAPI_View', [ 'title' => 'FAPI VIEW TITLE' ]);
	}

}