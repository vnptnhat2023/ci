<?php
namespace BAPI\Controllers;

class Home extends BaseController {

	// public function __construct()
	// {
	// 	$this->load->model('dashboard/Menu_model', 'menu');
	// 	$this->load->model('dashboard/Theme_model', 'dashboard_model', TRUE);
	// 	$this->load->library('my_theme');
	// 	$this->my_theme->initialize( 'backend', $this->dashboard_model->current_theme_option('backend') );
	// }

	public function index()
	{
		// echo __METHOD__;
		return bView('index', [ 'title' => 'Administrator management system' ]);
	}
}