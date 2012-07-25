<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>jpg to PNG converter version 2</title>
</head>

<body>
<form method="post" enctype="multipart/form-data">
<br />Please select the JPG picture
<br />Please post <input type="file" name="Junkinfile">
<br /><input type="submit">
</form>
<?php
if($_FILES['Junkinfile']){
$file = $_FILES['Junkinfile']['tmp_name'];
$pngFile = imagepng(imagecreatefromjpeg($file),"tmpFile.png");
echo "\n<br />The value of pngFile is ".$pngFile;
$fp = fopen('tmpFile.png','r');
$data = fread($fp,100000);
$base64data = base64_encode($data);
}
?>
<br />Here is the image <img src="tmpFile.png">
<br />
<br />
<br />Here is the image again, base 64
<br /><img src="data:image/png;base64,<?php echo $base64data;?>">
</body>
</html>
