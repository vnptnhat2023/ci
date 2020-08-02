<?php

namespace FAPI\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface as Req;
use CodeIgniter\HTTP\ResponseInterface as Res;
use Psr\Log\LoggerInterface as Log;

class BaseController extends Controller
{
	protected $helpers = [];

	public function initController(Req $req, Res $res, Log $log)
	{
		parent::initController($req, $res, $log);

		$this->session = \Config\Services::session();
	}

}
