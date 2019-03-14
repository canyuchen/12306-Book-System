<!doctype html>
<html>
	<head>
		<meta charset="utf-8"/>
		<title> 用户登录</title>
	</head>
	<center>
	<body>
	<img src="/figs/train.jpeg"  />
		<center>
		<H1>管理员登录</H1>
		<div>
			<form action="../cgi-bin/admi-signin.php" method="post">
			用户名：<br>
			<input type="text" name="adminame" required="required"><br>
			密码:<br>
			<input type="password" name="my-password" maxlength=20><br>
			<br>			
			<input type="submit" name="登录" value="登录" ><br>
			</form>
		</div>
	</body>
	</center>
		</center>
</html>
