<?php

$i18UserOrEmail = ucfirst(lang('NknAuth.labelUserOrEmail'));
$i18Password = ucfirst(lang('NknAuth.labelPassword'));
$i18Email = ucfirst(lang('NknAuth.labelEmail'));
$i18Captcha = ucfirst(lang('NknAuth.labelCaptcha'));
$i18RememberMe = ucfirst(lang('NknAuth.labelRememberMe'));
$i18HomePage = ucfirst(lang('NknAuth.labelHomePage'));
$i18ResetPasswordPage = ucfirst(lang('NknAuth.labelResetPasswordPage'));
$i18BtnLoginSubmit = ucfirst(lang('NknAuth.LabelBtnLoginSubmit'));
$i18BtnClear = ucfirst(lang('NknAuth.LabelBtnClear'));

?>

<!DOCTYPE html>
<html lang="vi">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= $i18BtnLoginSubmit ?></title>

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

		<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/vee-validate/2.2.15/vee-validate.min.js"></script>

		<style>
			[v-cloak] { display:none; }
		 		body {
        	/* background-image: url(https://source.unsplash.com/random); */
        	background-repeat: no-repeat;
	        background-position: center center;
	        background-size: cover;
	        margin: 0px;
	        padding: 0px;
	        min-height: 100vh;
        }

        .form-box {
          background: rgba(0,0,0,.8);
			    opacity: .9;
			    margin-top: 80px;
			    padding: 40px;
			    border-radius: 10px;
        }
        .form-box .form-group label{
          color: #fff;
        }
        .form-box .form-group input{
          border-radius: 0px;
          /*height: 35px;*/
          background: none;
          color: #fff;
        }
        .submit-btn{
          border-radius: 0px;
          background: #fff!important;
          color: red!important;
          padding: 10px 25px;
          font-weight: 600
        }
		</style>
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>

		<div class="container">
			<div class="row">
				<div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3"
				id="login-page" v-cloak>

					<?= form_open( base_url( 'login' ) ) ?>

					<div class="form-box">

						<?php
							if ( isset( $errors[ 0 ] ) ) {

								echo '<div class="alert alert-warning">';
									foreach ( $errors as $error ) { echo "<p>{$error}</p>"; }
								echo '</div>';

							}

							if ( isset( $success[ 0 ] ) ) {

								echo '<div class="alert alert-success">';
									foreach ( $success as $success ) { echo "<p>{$success}</p>"; }
								echo '</div>';

							}
						?>

						<?php if ( false === $result->success ) : ?>

						<div class="form-group" :class="{'has-warning': errors.has('username')}">
							<label for="username"><?= $i18UserOrEmail ?></label>

							<input type="text" class="form-control"
							v-validate="'required|min:5|max:128'"
							data-vv-as="<?= $i18UserOrEmail ?>"
							name="username"
							id="username"
							placeholder="<?= $i18UserOrEmail ?>"
							maxlength="128"
							value="<?php echo set_value('username') ?>"
							autocomplete="off"
							spellcheck="false">

							<span v-show="errors.has('username')" class="help-block">{{ errors.first('username') }}</span>
						</div>

						<div class="form-group" :class="{'has-warning': errors.has('password')}">
							<label for="password"><?= $i18Password ?></label>

							<input type="password"
							class="form-control"
							maxlength="32"
							v-validate="'required|min:5|max:64'"
							data-vv-as="<?= $i18Password ?>"
							name="password"
							id="password"
							placeholder="<?= $i18Password ?>"
							autocomplete="off"
							spellcheck="false"
							value="<?php echo set_value('password') ?>">

							<span v-show="errors.has('password')" class="help-block">{{ errors.first('password') }}</span>
						</div>

						<?php
						/*
							if ($show_captcha)
							{
								$show_captcha = captcha_ci(5, 115);
								$captcha_ci_err = form_error('ci_captcha', '<p class="text-danger small">', '</p>');
								echo <<< EOF
						<div class="form-group">
							<label for="captcha"><?= $i18RememberMe?></label>
							<div class="input-group">
						    <span class="input-group-addon" style="margin: 0; padding: 0;">{$show_captcha}</span>
						    <input type="text" class="form-control" id="captcha" name="ci_captcha" placeholder="<?= $i18RememberMe?>">
						  </div>
						  {$captcha_ci_err}
					  </div>
EOF;
							}
							*/
						?>

						<div class="form-group">
							<div class="checkbox">
								<label>
									<input
									type="checkbox"
									name="remember_me"
									value="1" <?php echo set_checkbox('remember_me', '1'); ?>>
									<span><?= $i18RememberMe ?></span>
								</label>
							</div>
						</div>

						<div class="form-group">
							<button
							type="submit"
							class="btn btn-primary"
							style="border-radius: none;"
							:disabled="errors.has('username') || errors.has('password')"><?= $i18BtnLoginSubmit ?></button>

							<button type="reset" class="btn btn-default" style="border-radius: none;" ><?= $i18BtnClear ?></button>
						</div>

						<div class="form-group">
							<span class="pull-right">
								<a href="<?php echo base_url('login/forgot') ?>">
									<span><?= $i18ResetPasswordPage ?></span>&nbsp;
									<span class="glyphicon glyphicon-arrow-right"></span>
								</a>
							</span>

							<span>
								<a href="<?php echo base_url() ?>">
									<span class="glyphicon glyphicon-arrow-left"></span>&nbsp;
									<span><?= $i18HomePage ?></span>
								</a>
							</span>
						</div>

						<?php endif; ?>

					</div>
					<?php echo form_close() ?>
				</div>
			</div>
		</div>

		<script>
			Vue.use(VeeValidate,{locale: 'vi'})
			new Vue({el:'#login-page'})
		</script>
	</body>
</html>