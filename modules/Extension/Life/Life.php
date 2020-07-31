<?php
namespace Ext\Life;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };

class Life extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'tester',
		'contact' => 'tester@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'Life extension',
		'slug' => 'Life',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'life-index'
			],
			[
				'method' => 'getMap',
				'name' => 'life-map'
			]
		]
	];

  public function index()
  {
    return ["return from Life::index" => 'Some value'];
	}
}