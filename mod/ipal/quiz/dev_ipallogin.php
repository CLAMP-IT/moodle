<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Moodle Remote Login</title>
</head>

<body onLoad="document.forms.dev_ipalLogin.submit()">
<form name="dev_ipalLogin" id="dev_ipalLogin" 
action="http://socrates.eckerd.edu/dev_ipal/login/index.php" method="post">
<input type="password" id="password" name="password" value="<?php echo $_GET['user'].'#'.$_GET['id'].'IPAL';?>">
<input type="text" id="username" name="username" value="<?php echo $_GET['user'];?>">
<input type="button" value="Log In" onclick="document.forms.dev_ipalLogin.submit()"/>
</form>

</body>
</html>