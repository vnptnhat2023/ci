<?php

$homeLink = anchor( base_url(), 'Back to home.' );

return [
	'homeLink' => $homeLink,

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
	'successResetPassword' => 'Your password has been successfully reset.',

	'labelUsername' => 'username',
	'labelUserOrEmail' => 'account name',
	'labelPassword' => 'account password',
	'labelEmail' => 'email address',
	'labelCaptcha' => 'captcha',
	'labelRememberMe' => 'remember me',
	'labelHomePage' => 'home',
	'labelResetPasswordPage' => 'reset pass',
	'LabelBtnLoginSubmit' => 'log in',
	'LabelBtnResetSubmit' => 'Reset',
	'LabelBtnClear' => 'clear',

	'placeholderUsername' => 'name',
	'placeholderUserOrEmail' => 'username or email',
	'placeholderPassword' => 'password',
	'placeholderCaptcha' => 'captcha code',

	'isAssoc' => 'Data must be an associative array.'
];