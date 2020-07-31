<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Title Page</title>
		
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

		<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/vee-validate/2.2.15/vee-validate.min.js"></script>
		<style>
			[v-cloak] { display:none; }
	 		body {
      	background-image: url(https://source.unsplash.com/random);
      	background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        margin: 0px;
        padding: 0px;
        min-height: 100vh;
      }
      .form-box {
        background: rgba(0,0,0,.8);
        background-repeat: no-repeat;
        background-position: center center;
		    opacity: .9;
		    margin-top: 80px;
		    padding: 40px;
		    border-radius: 10px;
      }
      /*.form-box {
        top: 50%;
		    left: 50%;
		    position: absolute;
		    width: 500px;
		    margin-top: 100px;
		    background: rgba(0,0,0,.8);
		    margin-left: -250px;
		    padding: 20px 30px;
		    line-height: 50px;
      }*/
      .form-box .form-group label{
        color: #fff;
      }
      .form-box .form-group input{
        border-radius: 0px;
        /*height: 35px;*/
        background: none;
        color: #fff;
      }
      .submit-btn {
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
				id="forget-password-page" v-cloak>
					<?php echo form_open('', '@submit="submit($event)"') ?>
					<div class="form-box">
						<?php
							if (isset($error[0]))
							{
								echo '<div class="alert alert-warning">';
								foreach ($error as $value) {
									echo "<p>{$value}</p>";
								}
								echo '</div>';
							}
						?>
						<p class="text-success">
							<?php echo isset($success) ? $success : null ?>
						</p>

						<div class="form-group" :class="{'has-warning': errors.has('username')}">
							<label for="username">Tên đăng nhập</label>
							<input type="text" class="form-control" maxlength="32"
							v-validate="'required|min:5|max:32'" data-vv-as="Tên đăng nhập"
							name="username" id="username" placeholder="Tên"
							value="<?php echo set_value('username') ?>" 
							autocomplete="off" spellcheck="false">
							<span v-show="errors.has('username')" class="help-block">{{ errors.first('username') }}</span>
						</div>
					
						<div class="form-group" :class="{'has-warning': errors.has('email')}">
							<label for="email">Địa chỉ email</label>
							<input type="email" class="form-control" maxlength="64"
							v-validate="'required|min:5|max:64|email'" data-vv-as="Mật khẩu"
							name="email" id="email" placeholder="Email" value="<?php echo set_value('email') ?>"
							autocomplete="off" spellcheck="false">
							<span v-show="errors.has('email')" class="help-block">{{ errors.first('email') }}</span>
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
							<button type="submit" class="btn btn-primary"
							:disabled="errors.has('username') || errors.has('email')">Đồng ý</button>
							<button type="reset" class="btn btn-default">Reset</button>
						</div>
						<div class="form-group">
							<a href="<?php echo base_url('login') ?>">
								<span class="glyphicon glyphicon-arrow-left"></span>&nbsp;
								<span>Quay lại trang đăng nhập</span>
							</a>
						</div>
					</div>
					<?php echo form_close() ?>

				</div>
			</div>
		</div>

		<script>
			Vue.use(VeeValidate,{locale: 'vi'})
			new Vue({el:'#forget-password-page'})
		</script>
	</body>
</html>