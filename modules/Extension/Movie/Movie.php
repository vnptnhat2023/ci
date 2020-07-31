<?php
namespace Ext\Movie;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };

class Movie extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'administrator',
		'contact' => 'administrator@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'Movie extension',
		'slug' => 'Movie',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'movie-index'
			],
			[
				'method' => 'getMap',
				'name' => 'movie-map'
			]
		]
	];

  public function index()
  {
    return $this->getParameters();
	}
}