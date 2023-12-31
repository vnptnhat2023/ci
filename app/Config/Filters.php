<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
	// Makes reading things below nicer,
	// and simpler to change out script that's used.
	public $aliases = [
		'csrf'     => \CodeIgniter\Filters\CSRF::class,
		'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
		'honeypot' => \CodeIgniter\Filters\Honeypot::class,
		'throttle' => \App\Filters\Throttle::class,

		'r2h' => \BAPI\Filters\Red2Horse::class,
		'r2h_permission' => \BAPI\Filters\Red2HorseAuthPermission::class,
		'r2h_role' => \BAPI\Filters\Red2HorseAuthRole::class,
	];

	// Always applied before every request
	public $globals = [
		'before' => [
			//'honeypot'
			// 'csrf',
		],
		'after'  => [
			'toolbar',
			//'honeypot'
		],
	];

	// Works on all of a particular HTTP method
	// (GET, POST, etc) as BEFORE filters only
	//     like: 'post' => ['CSRF', 'throttle'],
	public $methods = [
		'post' => [ 'throttle' ],
		'put' => [ 'throttle' ],
		'patch' => [ 'throttle' ],
		'delete' => [ 'throttle' ],
	];

	// List filter aliases and any before/after uri patterns
	// that they should run on, like:
	//    'isLoggedIn' => ['before' => ['account/*', 'profiles/*']],
	public $filters = [
		// 'R2hAuth' => ['before' => ['bapi/*']]
	];
}
