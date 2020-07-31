<?php

namespace BAPI\Config;

use BAPI\Config\Mixins\ConfigTrait;
use CodeIgniter\Config\BaseConfig;
/**
 * @if $name == 'ca' : $name_id = 0; $parent_id >= 0;
 * @else $name != 'ca' $parent_id = 0; $name_id > 0;
 */

 /** 05-05-20
	if ( $name == 'ca' )
	{
		$name = 'ca';  =>  $name_id = 0;
		$parent_id >= 0;// Find parent id and name
	}
	else
	{
		$parent_id = 0;// Find parent id and name
		$name_id > 0;  =>  $name != 'ca';
	}
 */

class Category extends BaseConfig
{
	use ConfigTrait;

  private const setting = [
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
				'name' => 'ca',# 'pa','po','cc'
				'name_id' => 0,
				'icon' => null,
				'keyword' => null,
				'parent_id' => 0,
				'status' => 'draff',
				'sort' => 0,
			],

			'fetch' => [
				'orderBy' => 'id',
				'direction' => 'ASC',
				'record' => 100
			],

			'option' => [
				'cat_info' => [
					'description' => true,
					'post_type' => true,
					'create_at' => false
				],
	
				'default_post' => 1,
				'pagination' => 'scroll',
				'no_item' => 'The post not found',
				'not_found' => 'Not found',
				'record' => 10,
	
				'post_info' => [
					'excerpt' => true,
					'post_type' => true,
					'create_at' => false,
					'author' => true,
					'tag' => true,
					'view' => true
				]
			]
		]
	];

	public function getRules(string $key = null) : array
	{
		$rules = [
			'id' => \Config\Validation::ruleInt(),

			'name' => [
				'label' => 'name',
				'rules' => 'required|in_list[ca,pa,po,cc]'
			],

			'name_id' => \Config\Validation::ruleInt( 'Name-Id', null, null, true ),

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
				'rules' => 'if_exist|trim|permit_empty|min_length[2]|max_length[32]|alpha_dash'
			],

			'keyword' => [
				'label' => 'keyword',
				'rules' => 'if_exist|trim|permit_empty|min_length[2]|max_length[128]'
			],

			'parent_id' => \Config\Validation::ruleInt( 'parent-Id', null, null, true ),

			'status' => [
				'label' => 'status',
				'rules' => 'required|in_list[publish,private,draff]'
			],

			'sort' => \Config\Validation::ruleInt( 'sort', null, null, true )
		];

		helper('array');

		return empty( $key ) ? $rules : ( dot_array_search( $key, $rules ) ?? $rules );
	}
}