<?php

namespace BAPI\Config;

use BAPI\Config\Mixins\ConfigTrait;
use CodeIgniter\Config\BaseConfig;

class Extension extends BaseConfig
{
	use ConfigTrait;

  private const setting = [

		'db' => [
			'create' => [
				'maximum_rows' => 1,
				'maximum_events' => 100
			],

			'delete' => [
				'soft' => false,
				'maximum_rows' => 1
			],

			'update' => [
				'maximum_rows' => 100
			],

			'fetch' => [
				'record' => 5,
				'orderBy' => 'id',
				'direction' => 'ASC'
			],

			# Fillable for Extension::Create (Controller)
			'fill' => [
				'author' => 'unknown',
				'contact' => 'unknown',
				'category_name' => 'unknown',
				'category_slug' => 'unknown',
				'description' => null,
				'status' => 'disable',
				'version' => '0.1'
			]
		],

		'cache' => [
			# Default extension cache name
			'name' => 'enabled',
			# Default extension cache prefix
			'prefix' => 'extStore_',
			# Default cache time-to-life
			'ttl' => 0
		]
	];

	public function getRules ( string $key = null )  : array
	{
		$rules = [

			'id' => \Config\Validation::ruleInt(),

			'author' => [
				'label' => 'author',
				'rules' => 'trim|required|min_length[5]|max_length[32]'
			],

			'contact' => [
				'label' => 'contact',
				'rules' => 'trim|required|min_length[7]|max_length[128]'
			],

			'category_name' => [
				'label' => 'category name',
				'rules' => 'trim|required|min_length[2]|max_length[32]'
			],

			'category_slug' => [
				'label' => 'category slug',
				'rules' => 'trim|required|min_length[2]|max_length[48]|alpha_dash'
			],

			'description' => [
				'label' => 'description',
				'rules' => 'if_exist|trim|permit_empty|min_length[2]|max_length[512]'
			],

			'title' => [
				'label' => 'title',
				'rules' => 'trim|required|min_length[4]|max_length[48]'
			],

			'slug' => [
				'label' => 'slug',
				'rules' => 'trim|required|min_length[4]|max_length[64]|alpha_dash'
			],

			'version' => [
				'label' => 'version',
				'rules' => 'trim|required|numeric'
			],

			'hashed_file' => [
				'label' => 'hash',
				'rules' => 'trim|required|max_length[80]'
			],

			'status' => [
				'label' => 'status',
				'rules' => 'required|in_list[enable,disable]'
			],

			'method' => [
				'label' => 'event method',
				'rules' => 'trim|required|min_length[2]|max_length[32]|alpha_dash'
			],

			'name' => [
				'label' => 'event name',
				'rules' => 'trim|required|min_length[2]|max_length[48]|alpha_dash'
			]
		];

		helper('array');

		return empty( $key ) ? $rules : dot_array_search( $key, $rules );
	}
}