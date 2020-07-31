<?php
namespace Ext\Download;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };

class Download extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'tester',
		'contact' => 'tester@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'Download extension',
		'slug' => 'Download',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'download-index'
			],
			[
				'method' => 'getMap',
				'name' => 'download-map'
			]
		]
	];

  public function index()
  {
    return [ 'return from Download::index' => 'Some value' ];
	}
}