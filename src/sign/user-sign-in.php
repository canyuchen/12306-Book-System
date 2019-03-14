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
		<H1>普通用户登录</H1>
		<!-- <div> -->
		<span>
			<form action="../cgi-bin/user-signin.php" method="post">
			用户名:<br>
			<input type="text" name="name" required="required"><br>
			密码:<br>
			<input type="password" name="my-password" maxlength=20><br>
		</span>
		<span>
		<br><input type="submit" name="登录" value="登录" ><br>
			</form>
		</span>
	</body>
	</center>
</html>
