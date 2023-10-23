<?php

namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;
use Config\Services;
use Red2Horse\Exception\ErrorUnauthorizedException;

class Red2HorseAuthRole implements FilterInterface
{
  public function before( req $request, $args = null )
  {
		$args = ( array ) $args;
		$isValid = Services::Red2HorseAuth()->withRole( $args );

    if ( ! $isValid )
    {
      throw new ErrorUnauthorizedException();
      // throw PageNotFoundException::forPageNotFound();
		}
  }

  public function after( req $request, res $response, $args = null )
  {
  }

}
