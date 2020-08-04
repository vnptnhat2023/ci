<!DOCTYPE html>
<html lang="vi">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Login Page</title>

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

		<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/vee-validate/2.2.15/vee-validate.min.js"></script>

		<style>
			[v-cloak] { display:none; }
		 		body {
        	/*background-image: url(https://source.unsplash.com/random);*/
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
					<?php echo form_open() ?>
					<div class="form-box">
						<?php
							if ( isset( $message_errors[0] ) ) {
								echo '<div class="alert alert-warning">';
								foreach ($message_errors as $errorMsg) { echo "<p>{$errorMsg}</p>"; }
								echo '</div>';
							}

							if ( isset( $message_success[0] ) ) {
								echo '<div class="alert alert-success">';
								foreach ($message_success as $successMsg) { echo "<p>{$successMsg}</p>"; }
								echo '</div>';
							}
						?>

						<div class="form-group" :class="{'has-warning': errors.has('username')}">
							<label for="username">Tên đăng nhập</label>
							<input type="text" class="form-control"
							v-validate="'required|min:5|max:32'" data-vv-as="Tên đăng nhập"
							name="username" id="username" placeholder="Tên" maxlength="32"
							value="<?php echo set_value('username') ?>" autocomplete="off" spellcheck="false">
							<span v-show="errors.has('username')" class="help-block">{{ errors.first('username') }}</span>
						</div>

						<div class="form-group" :class="{'has-warning': errors.has('password')}">
							<label for="password">Mật khẩu</label>
							<input type="password" class="form-control" maxlength="32"
							v-validate="'required|min:5|max:64'" data-vv-as="Mật khẩu"
							name="password" id="password" placeholder="Mật khẩu" autocomplete="off" spellcheck="false"
							value="<?php echo set_value('password') ?>">
							<span v-show="errors.has('password')" class="help-block">{{ errors.first('password') }}</span>
						</div>

						<?php
// 							if ($show_captcha)
// 							{
// 								$show_captcha = captcha_ci(5, 115);
// 								$captcha_ci_err = form_error('ci_captcha', '<p class="text-danger small">', '</p>');
// 								echo <<< EOF
// 						<div class="form-group">
// 							<label for="captcha">Captcha</label>
// 							<div class="input-group">
// 						    <span class="input-group-addon" style="margin: 0; padding: 0;">{$show_captcha}</span>
// 						    <input type="text" class="form-control" id="captcha" name="ci_captcha" placeholder="Mã kiểm tra">
// 						  </div>
// 						  {$captcha_ci_err}
// 					  </div>
// EOF;
// 							}
						?>

						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="remember_me"
									value="1" <?php echo set_checkbox('remember_me', '1'); ?>>
									<span>Ghi nhớ tôi</span>
								</label>
							</div>
						</div>

						<div class="form-group">
							<button type="submit" class="btn btn-primary" style="border-radius: none;"
							:disabled="errors.has('username') || errors.has('password')">Đăng nhập</button>
							<button type="reset" class="btn btn-default" style="border-radius: none;" >Reset</button>
						</div>
						<div class="form-group">
							<span class="pull-right">
								<a href="<?php echo base_url('login/forgot') ?>">
									<span>Quên mật khẩu?</span>&nbsp;
									<span class="glyphicon glyphicon-arrow-right"></span>
								</a>
							</span>
							<span>
								<a href="<?php echo base_url() ?>">
									<span class="glyphicon glyphicon-arrow-left"></span>&nbsp;
									<span>Trang chủ</span>
								</a>
							</span>
						</div>
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