<?php

namespace BAPI\Config;

use BAPI\Config\Mixins\ConfigTrait;
use CodeIgniter\Config\BaseConfig;

class GeneralGroup extends BaseConfig
{
	use ConfigTrait;

	# Not using
  private const setting = [
		'db' => [
			'create' => [
				'maximum_rows' => 1,# Not using
			],

			'delete' => [
				'soft' => true,
				'maximum_rows' => 1
			],

			'update' => [
				'maximum_rows' => 1
			],

			'fill' => [
				'status' => 'active'
			],

			'fetch' => [
				'record' => 10,
				'order_by' => 'id',
				'direction' => 'ASC'
			],
		]
	];

	public function getRules ( string $key = null ) : array
	{
		$rules = [

			'id' => \Config\Validation::ruleInt( 'id', 'required' ),

			'title' => [
				'label' => 'title',
				'rules' => 'trim|required|min_length[2]|max_length[32]'
			],

			'slug' => [
				'label' => 'slug',
				'rules' => 'trim|required|min_length[2]|max_length[48]|alpha_dash'
			],

			'status' => [
				'label' => 'status',
				'rules' => 'required|in_list[active,inactive]'
			]
		];

		helper('array');

		return empty( $key ) ? $rules : dot_array_search( $key, $rules );
	}
}