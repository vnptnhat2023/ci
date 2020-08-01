<?php

namespace BAPI\Config;

use BAPI\Config\Mixins\ConfigTrait;
use CodeIgniter\Config\BaseConfig;

class Page extends BaseConfig
{
	use ConfigTrait;

	private const setting = [
		# --- Will be soon
		// 'default_page' => 'home',

		'db' => [
			'create' => [
				'maximum_rows' => 1
			],

			'update' => [
				'maximum_rows' => 1
			],

			'delete' => [
				'soft' => true,
				'maximum_rows' => 1
			],

			'fill' => [
				'icon' => null,
				'content' => null,
				'advanced_content' => null,
				'advanced_position' => 'bottom',
				'parent_id' => 0,
				'status' => 'draff',
				'sort' => 0
			]
		],

		// 'cache' => []
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

			'icon' => [
				'label' => 'icon',
				'rules' => 'if_exist|trim|min_length[2]|max_length[32]|alpha_space'
			],

			'content' => [
				'label' => 'content',
				'rules' => 'if_exist|trim|permit_empty'
			],

			'advanced_content' => [
				'label' => 'advanced content',
				'rules' => 'if_exist|trim|permit_empty'
			],

			'advanced_position' => [
				'label' => 'advanced position',
				'rules' => 'required|in_list[top,bottom]'
			],

			'parent_id' => \Config\Validation::ruleInt( 'parent id', null, null, true ),

			'status' => [
				'label' => 'status',
				'rules' => 'required|in_list[publish,private,draff]'
			],

			'sort' => \Config\Validation::ruleInt( 'sort', null, null, true )
		];

		helper('array');

		return empty( $key ) ? $rules : dot_array_search( $key, $rules );
	}
}