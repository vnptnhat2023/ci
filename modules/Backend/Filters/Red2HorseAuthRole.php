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
		// var_dump( $args[0][0] === '!' );
		// die;
		// $isValid = Services::Red2HorseAuth()->withRole( ( array ) $args, false );
		$isValid = Services::Red2HorseAuth()->withRole( ( array ) $args );

    if ( false === $isValid ) {
      throw PageNotFoundException::forPageNotFound();
		}
  }

  public function after( req $request, res $response, $args = null )
  {
  }

}
