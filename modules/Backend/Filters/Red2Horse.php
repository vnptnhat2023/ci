<?php

namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;
use Config\Services;

class Red2Horse implements FilterInterface
{
	public function before( req $request, $args = null )
	{
		$auth = Services::Red2HorseAuth();
		$isValidRole = $auth->withRole( ( array ) $args );

		if ( ! $isValidRole )
		{
			if ( ! $auth->withPermission( ( array ) $args ) )
			{
				throw PageNotFoundException::forPageNotFound();
			}
		}
	}

	public function after( req $request, res $response, $args = null )
	{
	}

}
