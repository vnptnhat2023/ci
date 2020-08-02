<?php

namespace BAPI\Controllers;

use CodeIgniter\Controller;
use \CodeIgniter\HTTP\RequestInterface;
use \CodeIgniter\HTTP\ResponseInterface;
use \Psr\Log\LoggerInterface;

class BaseController extends Controller
{
	protected $helpers = ['array'];
	protected $modelName;
	protected $model;
	protected $session;

	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		$this->session = \Config\Services::session();

		if ( ! empty($this->modelName) ) {
			$this->model = model($this->modelName);
		}

	}
}