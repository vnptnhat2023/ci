<?php
namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;

class NknAuth implements FilterInterface
{

  public function before( req $request, $arguments = null )
  {
    if ( ! \Config\Services::NknAuth()->hasPermission( $arguments ) ) {
      throw PageNotFoundException::forPageNotFound();
    }
  }

  public function after( req $request, res $response, $arguments = null )
  {
  }

}
