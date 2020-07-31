<?php
namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;

class NKNAuth implements FilterInterface
{

  public function before( req $request, ...$params )
  {
    $permission = $params[0] ?? [];
    $nknAuth = \Config\Services::NknAuth();

    if ( ! $nknAuth->hasPermission( $permission ) ) {
      throw PageNotFoundException::forPageNotFound();
    }
  }

  public function after( req $request, res $response )
  {
  }

}
