<?php

$homeLink = sprintf( '%s', anchor( base_url(), 'Back to home.' ) );

return [
	'homeLink' => $homeLink,
	'login' => 'login',
	'logout' => 'logout',
	'resetPassword' => 'reset password',

	'errorCookieUpdate' => 'Validated cookie, when update userId: {0}',
	'errorNeedLoggedIn' => 'You have not login yet. ',
	'errorIncorrectInformation' => 'Incorrect information.',
	'errorNotReadyYet' => 'The current account is in status `{0}` has not been activated yet.',
	'errorThrottleLimitedTime' => 'Please try to login again after {num} {type}',

	'noteDenyRequestPassword' => 'You are logged in, so you are not authorized to use this function.',
	'noteLoggedInAnotherPlatform' => 'Your account has been logged-in with another platform.',

	'successLogged' => 'You are already logged in. ' . $homeLink,
	'successLoggedWithUsername' => 'Hi {0}, you are already logged in. ' . $homeLink,
	'successLogoutWithUsername' => '{0} you have been logged out. ' . $homeLink,
	'successLogout' => 'You have been logged out. ' . $homeLink,
	'successResetPassword' => 'Hi {0}, your password has been reset to {1}.',

	'labelUsername' => 'username',
	'labelUserOrEmail' => 'email',
	'labelPassword' => 'password',
	'labelEmail' => 'email',
	'labelCaptcha' => 'captcha',
	'labelRememberMe' => 'remember me',
	'labelHomePage' => 'home',
	'labelResetPasswordPage' => 'reset password',
	'LabelBtnLoginSubmit' => 'log in',
	'LabelBtnResetSubmit' => 'reset',
	'LabelBtnClear' => 'clear',

	'placeholderUsername' => 'name',
	'placeholderUserOrEmail' => 'name',
	'placeholderPassword' => 'password',
	'placeholderCaptcha' => 'captcha',

	'isAssoc' => 'Data must be an associative array.'
];