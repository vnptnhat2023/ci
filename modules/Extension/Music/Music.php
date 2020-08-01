<?php
namespace Ext\Music;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };

class Music extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'unknown',
		'contact' => 'unknown@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'music extension',
		'slug' => 'Music',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'music-index'
			],
			[
				'method' => 'getMap',
				'name' => 'music-map'
			]
		]
	];

  public function index()
  {
    return $this->getParameter();
	}
}