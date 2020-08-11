<?php

namespace BAPI\Controllers;

use CodeIgniter\Controller;
use \CodeIgniter\HTTP\{RequestInterface as Req, ResponseInterface as Res};
use CodeIgniter\Model;
use CodeIgniter\Session\Session;
use \Psr\Log\LoggerInterface as Log;

class BaseController extends Controller
{
	// protected $helpers = ['array'];
	protected $modelName;
	protected Model $model;
	protected Session $session;

	public function initController(Req $req, Res $res, Log $log)
	{
		parent::initController($req, $res, $log);

		$this->session = \Config\Services::session();

		if ( ! empty($this->modelName) ) {
			$this->model = model( $this->modelName );
		}

	}
}