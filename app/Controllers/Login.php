<?php namespace App\Controllers;

class Login extends BaseController {

	private $NknAuth;

	public function __construct()
	{
		$this->NknAuth = new \App\Libraries\NknAuth;
		helper(['form', 'array']);
	}

	public function index()
	{
		$data = $this->NknAuth->login()->as_array();

		if ( $data['load_view'] )
		{
			if ( $data['wrong'] ) {
				$data['error'][] = 'Tên đăng nhập hoặc mật khẩu không chính xác';
			}
			return view('login/login', $data);
		}
		else if( $data['banned'] OR $data['inactive'] )
		{
			echo 'Tải khoản hiện tại đang ở trạng thái '. ($data['banned'] ? '"khóa".' : '"chưa kích hoạt".');
		}
		else if ( $data['success'] )
		{
			$user = $this->NknAuth->get_userdata();
			$update = [
				'last_login' => $this->request->getIPAddress(),
				'last_activity' => date('Y-m-d')
			];
			# set last login
			$this->NknAuth->builder->update( $update, [ 'id' => $user['id'] ], 1 );
			echo anchor(base_url(), 'Back to Homepage');
		}
		else if ( $data['was_limited'] )
		{
			echo "Bạn hãy thử đăng nhập lại sau 30 phút";
		}

		// d( session('oknkn') );
	}

	public function forgot()
	{
		$data = $this->NknAuth->forgot_password()->as_array();

		if ( $data['load_view'] )
		{
			if ($data['wrong']) {
				$data['error'][] = '<strong>Tên</strong> hoặc <strong>Email</strong> không khớp';
			}

			return view('login/forgot', $data);
		}
		else if ( $data['success'] )
		{
			echo 'Sent mail at here';
		}
		else if ( $data['forgot_password_denny'] )
		{
			echo 'Bạn đang đăng nhập, nên không được phép sử dụng chức năng này.';
		}
		else if ( $data['was_limited'] )
		{# Mặc định timeout là 30;
			echo 'Bạn hãy thử khôi phục lại mật khẩu sau 30 phút';
		}
	}

	public function logout()
	{
		$this->NknAuth->logout();
		echo anchor( base_url(), 'Back to homepage' );
		// return redirect('App\Controllers\Blog::index');
	}

	private function redirect_to()
	{
		// if ( $this->request->getGet('r') ) {
		// 	redirect( base64_decode( $this->request->getGet('r') ) );
		// }
		return redirect('App\Controllers\Blog::index');
		exit(0);
	}

}