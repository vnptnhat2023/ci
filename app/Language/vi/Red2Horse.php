<?php

$homeLink = sprintf( '%s', anchor( base_url(), 'quay lại trang chủ.' ) );

return [
	'ci_captcha' => 'Sai mã xác nhận.',
	'valid_json' => '{field} không đúng định dạng "json".',
	'is_array' => '{field} phải chứa đúng định dạng mảng một chiều.',
	'isAssoc' => '{field} phải chứa đúng định dạng mảng hai chiều.',
	'inPermission' => '{field} không chứa trong danh sách quyền.',

	'homeLink' => $homeLink,
	'login' => 'Đăng nhập - lnbl',
	'logout' => 'Thoát',
	'resetPassword' => 'Quên mật khẩu - lnbl',

	'number' => 'một ít',

	'errorCookieUpdate' => 'Mã ghi nhớ ( cookie ) hợp lệ nhưng lỗi ( không cập nhật được; UserId: {0} ).',
	'errorNeedLoggedIn' => 'Bạn chưa đăng nhập. ',
	'errorIncorrectInformation' => 'Sai thông tin.',
	'errorNotReadyYet' => 'Tài khoản đang ở trạng thái `{0}`( chưa sử dụng được ).',
	'errorThrottleLimitedTime' => 'Mời bạn quay lại sau: {number} phút',

	'noteDenyRequestPassword' => 'Bạn đã đăng nhập, nhưng không đủ quyền.',
	'noteLoggedInAnotherPlatform' => 'Tài khoản đã đăng nhập từ một nền tảng khác.',

	'successLogged' => 'Bạn đã đăng nhập! ' . $homeLink,
	'successLoggedWithUsername' => 'Chào {0}, bạn đã đăng nhập! ' . $homeLink,
	'successLogoutWithUsername' => '{0} bạn đã thoát! ' . $homeLink,
	'successLogout' => 'Bạn đã thoát! ' . $homeLink,
	'successResetPassword' => 'Chào {0} !, thư của bạn đã gửi tới: {1}.',

	'labelUsername' => 'Tên đăng nhập',
	'labelUserOrEmail' => 'Tên đăng nhập địa chỉ hộp thư',
	'labelPassword' => 'Mật khẩu',
	'labelEmail' => 'Email',
	'labelCaptcha' => 'Xác nhận',
	'labelRememberMe' => 'Ghi nhớ',
	'labelHomePage' => 'Trang chủ',
	'labelResetPasswordPage' => 'Quên mật khẩu',
	'LabelBtnLoginSubmit' => 'Đăng nhập',
	'LabelBtnResetSubmit' => 'Chạy',
	'LabelBtnClear' => 'Xóa',

	'placeholderUsername' => 'Tên',
	'placeholderUserOrEmail' => 'Tên',
	'placeholderPassword' => 'Mật khẩu',
	'placeholderCaptcha' => 'Xác nhận',

	'id' => 'Chỉ mục',
	'status' => 'trạng thái',
	'activity' => 'kích hoạt khi',
	'last_login' => 'đăng nhập lần cuối',
	'created_at' => 'đã tạo khi',
	'updated_at' => 'đã sửa khi',
	'deleted_at' => 'đã xóa khi',
	'session_id' => 'session id',
	'selector' => 'selector',
	'token' => 'dòng chữ ngẫu nhiên',
	'ci_captcha' => 'Sai chữ ngẫu nhiên',
];