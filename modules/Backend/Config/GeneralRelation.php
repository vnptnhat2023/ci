<?php

namespace BAPI\Config;

use BAPI\Config\Mixins\ConfigTrait;
use CodeIgniter\Config\BaseConfig;

class GeneralRelation extends BaseConfig
{
	use ConfigTrait;

  private const setting = [
		'fetch' => [
			'record' => 10,
			'orderBy' => 'id',// []
			'direction' => 'DESC'
		]
	];

	public function getRules ( string $key = null ) : array
	{

		$rules = [

			'id' => \Config\Validation::ruleInt( 'id', 'required' ),

			'name' => [
				'label' => 'Name',
				'rules' => 'trim|required|exact_length[2]|alpha_dash'
			],

			'name_id' => \Config\Validation::ruleInt( 'name id', null, null, true ),

			'ggid' => \Config\Validation::ruleInt( 'general group id', 'required' ),

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
