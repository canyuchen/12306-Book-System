<!doctype html>
<html>
	<head>
		<meta charset="utf-8"/>
		<title> 用户注册</title>
	</head>
	<script language="javascript">
		var name = form1.name
	</script>
	<center>
	<body>
	<img src="/figs/train.jpeg"  />
			<center>
			<H1>用户注册</H1>

			<span>
			<form method="post" action="../cgi-bin/user-signup.php">
				姓名<br>
				<input type="text" name="my-name" maxlength=20><br>
				身份证号<br>
				<input type="text" name="my-number" maxlength=18><br>
				手机号<br>
				<input type="text" name="my-phone" maxlength=11 ><br>
				信用卡<br>
				<input type="text" name="my-card" maxlength=16><br>
				用户名<br>
				<input type="text" name="my-username" maxlength=20><br>
				密码<br>
				<input type="password" name="my-password" maxlength=20><br>
			</span>

			<span>
				<br><input type="submit" name="sign-up" value="注册">
			</span>

			</form>
			</center>
	</body>
	<center>
</html>
