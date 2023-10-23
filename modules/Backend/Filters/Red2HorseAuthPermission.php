<?php

namespace BAPI\Filters;

// use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;
use Config\Services;
use Red2Horse\Exception\ErrorUnauthorizedException;

class Red2HorseAuthPermission implements FilterInterface
{
	public function before( req $request, $arguments = null )
	{
		$isValid = Services::Red2HorseAuth() ->withPermission( ( array ) $arguments );

		if ( ! $isValid )
		{
			throw new ErrorUnauthorizedException();
		}
	}

	public function after( req $request, res $response, $arguments = null )
	{
	}

}
