<?php

namespace BAPI\Config;

use CodeIgniter\Config\BaseConfig;

class Theme extends BaseConfig
{
  public const backend = [
    'author' => 'administrator',
    'email' => 'webmaster@local.host',
    'name' => 'Backend default theme',
    'content' => 'writing',
    'site_title' => 'N.K.N backend',
    'site_slogan' => '',
    'asset_path' => 'default',
    'view_path' => 'default',
    'version' => '0.1'
  ];

  public const frontend = [
    'author' => 'administrator',
    'email' => 'webmaster@local.host',
    'name' => 'Frontend default theme',
    'content' => 'writing',
    'site_title' => 'N.K.N',
    'site_slogan' => '',
    'asset_path' => 'default',
    'view_path' => 'default',
    'version' => '0.1'
  ];

	public function getRules(string $key = null) : array
	{
		$rules = [
			'asset_path' => 'trim|required|min_length[4]|max_length[128]|isPath',
			'author' => 'trim|required|min_length[5]|max_length[64]|alpha_numeric_punct',
			'content' => 'trim|max_length[512]',
			'email' => 'trim|min_length[5]|max_length[32]|valid_email',
			'name' => 'trim|required|min_length[4]|max_length[64]|alpha_numeric_space',
			'view_path' => 'trim|required|min_length[4]|max_length[128]|isPath',
			'version' => 'required|numeric|max_length[11]'
		];

		helper('array');

		return empty( $key ) ? $rules : ( dot_array_search( $key, $rules ) ?? $rules );
	}
}