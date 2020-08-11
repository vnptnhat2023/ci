<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use \CodeIgniter\HTTP\{RequestInterface as Req, ResponseInterface as Res};
use CodeIgniter\Session\Session;
use Config\Services;
use \Psr\Log\LoggerInterface as Log;

class BaseController extends Controller
{

	protected $helpers = [];

	protected Session $session;

	public function initController(Req $req,Res $res, Log $log)
	{
		parent::initController($req, $res, $log);

		$this->session = Services::session();
	}

}
