<?php
namespace Ext\Book;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };
use CodeIgniter\Router\RouteCollection;

class Book extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'unknown',
		'contact' => 'ex@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'book example extension',
		'slug' => 'Book',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'book-index'
			],
			[
				'method' => 'getMap',
				'name' => 'book-map'
			]
		]
	];

  public function index()
  {
    return $this->getParameters();
  }

	public function map(string $key = null)
	{
    return self::getMap($key);
	}

	# --- Todo: new class { load routerCollection, request, response, make another MODULES ^^ }
	public static function getRoutes(RouteCollection $routes)
	{
		$routes->get( 'setting/extension', 'Setting::index',
		['namespace' => '\Ext\Book\Controllers'] );

		$routes->get( 'book_route123', function() {
			return fView( 'FAPI_View', [ 'title' => ucfirst( self::map['name'] ) ] );
		} );
	}
}