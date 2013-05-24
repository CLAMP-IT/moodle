<?php
print ("<!DOCTYPE HTML>");
?>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
<script type="text/javascript" src="shiblogout.js"></script>

<script type="text/javascript">
        doShibbolethLogout('<?php echo($_SERVER["Shib-Identity-Provider-LogoutURL"])?>', logoutCallback);

        function logoutCallback(status) {
                console.log('logoutCallback - ' + status);
        }
</script>
<?php
global $backto;
$backto = $_SERVER["SERVER_NAME"];
print ("
<html>
     <head>
     <style type=\"text/css\">
     body
{
background-image:url('twoodle.smith.edu/logout/sc_campusmap1922-blue-logout.jpg');

background-repeat:no-repeat;
background-position:left top;
} 

h3
{
background-color:#FF3030;
color:white;
text-align:center;
}
</style>
        <title>Shibboleth Logout - Smith College</title>
    </head>
	<body leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">
	<center>

<table bgcolor=#FFFFFF border=\"0\"  width=\"550\" align=\"center\">

	<tr>
  <td valign=\"top\" align=center>	<img src=\"https://idp.smith.edu/idp/images/logolarge.gif\" />  
                           <br><br><font color=\"#00386B\">You have been logged out of your application.</font><br><br>
                          <b> <font color=\"#FF3030\">This will not log you out of any other open applications.</font></b>
                            
                   <h3> Please quit your browser to finish the logout process</h3>
<center><a href=\"http://$backto\">
Return to previous page
</a></center>         
			  </tr>
			  <tr><td> </td></tr>
			</table>
<br><br>
");
print ("<b><h3>Server array</h3></b>");
foreach ($_SERVER as $key => $value) {
  print ("<br>");
  print ("<b>$key:</b>&nbsp; $value");
}
print ("<br>");
print ("<b><h3>Cookies array</h3></b>");
foreach ($_COOKIE as $key => $value) {
  print ("<br>");
  print ("<b>$key:</b>&nbsp; $value");
}
print ("<br>");
print ("<b><h3>Session array</h3></b>");
foreach ($_SESSION as $key => $value) {
  print ("<br>");
  print ("<b>$key:</b>&nbsp; $value");
}

print ("
    </body>
</html>
");

?>  
