<?php

namespace BAPI\Controllers;
use \CodeIgniter\HTTP\RequestInterface;
use \CodeIgniter\HTTP\ResponseInterface;
use \Psr\Log\LoggerInterface;

class BaseController extends \CodeIgniter\Controller
{
	protected $helpers = ['array'];
	protected $modelName;
	protected $model;

	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		if ( ! empty($this->modelName) ) {
			$this->model = model($this->modelName);
		}

	}
}