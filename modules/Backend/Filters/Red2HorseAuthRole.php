<?php

namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;
use Config\Services;

class Red2HorseAuthRole implements FilterInterface
{
  public function before( req $request, $arguments = null )
  {
		$isValid = Services::Red2HorseAuth()
		->withRole( ( string ) $arguments );

    if ( false === $isValid ) {
      throw PageNotFoundException::forPageNotFound();
		}

		// return $request;
  }

  public function after( req $request, res $response, $arguments = null )
  {
  }

}
