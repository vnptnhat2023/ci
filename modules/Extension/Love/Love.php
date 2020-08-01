<?php
namespace Ext\Love;

use App\Libraries\Ext\{ AbstractExtension, ExtensionTrait };

class Love extends AbstractExtension
{
	use ExtensionTrait;

	private const map = [
		'author' => 'administrator',
		'contact' => 'administrator@local.host',
		'category_name' => 'unknown',
		'description' => 'unknown',
		'name' => 'Love extension',
		'slug' => 'Love',
		'version' => '0.1',
		'events' => [
			[
				'method' => 'index',
				'name' => 'Love-index'
			],
			[
				'method' => 'getMap',
				'name' => 'Love-map'
			],
			[
				'method' => 'love',
				'name' => 'Love-love'
			]
		]
	];

	protected $data = [];

  public function index()
  {
    return $this->getParameter();
  }

  public function love(int $id = 1)
  {
		if ( empty( $this->data['extension'] ) )
		{
			$extensionModel = new \BAPI\Models\Extension\Crud();
			$this->data['extension'] = $extensionModel->select('title')->find($id);
		}

		return $this->data;
	}
}