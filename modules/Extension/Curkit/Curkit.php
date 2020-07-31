<?php
namespace Ext\Curkit;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };

class Curkit extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'Curkit',
		'contact' => 'ex@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'Curkit extension',
		'slug' => 'Curkit',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'curkit-event'
			],
			[
				'method' => 'getMap',
				'name' => 'curkit-map'
			]
		]
	];

  public function index()
  {
    return $this->getParameters();
	}
}