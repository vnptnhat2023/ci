<?php

if ( !function_exists('base_hostname')) {
	function base_hostname() {
		$url_parts = parse_url(base_url());
		return str_replace('www.', '', $url_parts['host']);
	}
}

if ( !function_exists('url_hostname')) {
	function url_hostname($url = '') {
		if (trim($url)) {
			$user_url = parse_url($url);
			return str_replace('www.', '', $user_url['host']);
		}
		return false;
	}
}

if ( !function_exists('compare_hostname')) {
	function compare_hostname($url = '') {
		if ($url) {
			$url_parts = parse_url(current_url());
			$hostname = str_replace('www.', '', $url_parts['host']);

			$user_url = parse_url($url);
			$url_name = str_replace('www.', '', $user_url['host']);
			return $hostname == $url_name;
		}
		return false;
	}
}