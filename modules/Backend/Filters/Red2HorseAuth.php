<?php
namespace BAPI\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;

use CodeIgniter\HTTP\RequestInterface as req;
use CodeIgniter\HTTP\ResponseInterface as res;
use Config\Services;

class Red2HorseAuth implements FilterInterface
{

  public function before( req $request, $arguments = null )
  {
		// $f = explode( '.', $arguments[0] );
		// // dd($arguments);
		// dd( $f[ array_key_last( $f ) ] );
		// $args = [];
		// dotArrayAssign( $args, $arguments[0], 'test' );
		// dd( $args );

    if ( ! Services::Red2HorseAuth()->withPermission( $arguments ?: [] ) ) {
      throw PageNotFoundException::forPageNotFound();
    }
  }

  public function after( req $request, res $response, $arguments = null )
  {
  }

}
