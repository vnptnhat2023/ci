<?php

namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;
use Config\Services;

class Red2HorseAuthRole implements FilterInterface
{
  public function before( req $request, $args = null )
  {
		$isValid = Services::Red2HorseAuth()->withRole( ( array ) $args, false );

    if ( false === $isValid ) {
      throw PageNotFoundException::forPageNotFound();
		}
  }

  public function after( req $request, res $response, $args = null )
  {
  }

}
