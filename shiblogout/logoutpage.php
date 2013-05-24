<?php
print ("
<!DOCTYPE HTML>
<html>
     <head>
     <style type="text/css">
     body
{
background-image:url('./images/sc_campusmap1922-blue-logout.jpg');
background-repeat:no-repeat;
background-position:left top;
} 
table
{
position:fixed;
top:125px;
left:65px;
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
  <td valign=\"top\" align=center>	<img src=\"./images/logolarge.gif\" />  
                           <br><br><font color=\"#00386B\">You have been logged out of your application.</font><br><br>
                          <b> <font color=\"#FF3030\">This will not log you out of any other open applications.</font></b>
                            
                   <h3> Please quit your browser to finish the logout process</h3>
<center><a href=\"http://$backto\">
Return to previous page
</a>      </center>         
			  </tr>
			  <tr><td> </td></tr>
			</table>	
    </body>
</html>
");
?>