<?php
namespace Ext\Book\Controllers;

use App\Controllers\BaseController;

class Setting extends BaseController
{
	public function index()
	{
		echo self::class;
	}

	public function more()
	{
		return fView( 'FAPI_View', [ 'title' => 'This is an awesome title !' ] );
	}
}
