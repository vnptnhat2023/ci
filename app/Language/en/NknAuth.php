<?php

$homeLink = anchor( base_url(), 'Click here go to home.' );

return [
  'errorIncorrectInformation' => 'Incorrect information.',
	'errorNotReadyYet' => 'The current account is in status [{0}] has not been activated yet.',
	'errorThrottleLimitedTime' => 'Please try to login again after [{0}] minutes',

	'noteDenyRequestPassword' => 'You are logged in, so you are not authorized to use this function.',

	'successLogged' => 'You are already logged in. ' . $homeLink,
	'successLoggedWithUsername' => 'Hello {0}, you are already logged in. ' . $homeLink,
	'successLogoutWithUsername' => 'Bye {0} you have been logged out. ' . $homeLink,
	'successLogout' => 'You have been logged out. ' . $homeLink,
	'successResetPassword' => 'Your password has been successfully reset.',

	'labelUsername' => 'name',
	'labelUserOrEmail' => 'name or email',
	'labelPassword' => 'password',
	'labelEmail' => 'email',
	'labelCaptcha' => 'captcha',
	'labelRememberMe' => 'remember me',
	'labelHomePage' => 'home',
	'labelResetPasswordPage' => 'reset password',
	'LabelBtnLoginSubmit' => 'log in',
	'LabelBtnResetSubmit' => 'Reset',
	'LabelBtnClear' => 'clear',
];