<?php
namespace Ext\Forum;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };

class Forum extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'administrator',
		'contact' => 'administrator@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'Forum extension',
		'slug' => 'Forum',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'forum-index'
			],
			[
				'method' => 'getMap',
				'name' => 'forum-map'
			]
		]
	];

  public function index()
  {
    return [ 'return from Forum::index' => 'Some value' ];
	}
}