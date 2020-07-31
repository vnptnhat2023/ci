<?php
namespace Ext\Health;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };

class Health extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'tester',
		'contact' => 'tester@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'Health extension',
		'slug' => 'Health',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'health-index'
			],
			[
				'method' => 'getMap',
				'name' => 'health-map'
			]
		]
	];

  public function index()
  {
    return ["return from Health::index" => 'Some value'];
	}
}