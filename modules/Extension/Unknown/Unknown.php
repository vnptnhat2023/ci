<?php
namespace Ext\Unknown;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };

class Unknown extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'administrator',
		'contact' => 'administrator@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'Unknown extension',
		'slug' => 'Unknown',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'unknown-index'
			],
			[
				'method' => 'getMap',
				'name' => 'unknown-map'
			]
		]
	];

  public function index()
  {
    return [ 'return from Unknown::index' => 'Some value' ];
	}
}