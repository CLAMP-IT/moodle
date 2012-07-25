<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>XML Parser</title>
</head>

<body>
<form method="post" enctype="multipart/form-data">
<br />Please post <input type="file" name="Junkinfile">
<br /><input type="submit">

</form>
<?php
if($_FILES['Junkinfile']){
echo "\n<br />Here is the result\n<br />";
$fp = fopen($_FILES['Junkinfile']['tmp_name'],'r');
$data = fread($fp,80000);
fclose($fp);
echo $data;
echo "<br />Here is the XML parsed data<br />";
//From http://www.phpfreaks.com/tutorial/handling-xml-data

function parse_recursive(SimpleXMLElement $element, $level = 0)
{
        $indent     = str_repeat("\t", $level); // determine how much we'll indent
        
        $value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
        $attributes = $element->attributes();   // get all attributes
        $children   = $element->children();     // get all children
        
        echo "{$indent}Parsing '{$element->getName()}'...".PHP_EOL;
        if(count($children) == 0 && !empty($value)) // only show value if there is any and if there aren't any children
        {
                echo "{$indent}Value: {$element}".PHP_EOL;
        }
        
        // only show attributes if there are any
        if(count($attributes) > 0)
        {
                echo $indent.'Has '.count($attributes).' attribute(s):'.PHP_EOL;
                foreach($attributes as $attribute)
                {
                        echo "{$indent}- {$attribute->getName()}: {$attribute}".PHP_EOL;
                }
        }
        
        // only show children if there are any
        if(count($children))
        {
                echo $indent.'Has '.count($children).' child(ren):'.PHP_EOL;
                foreach($children as $child)
                {
                        parse_recursive($child, $level+1); // recursion :)
                }
        }
        
        echo $indent.PHP_EOL; // just to make it "cleaner"
}

$parsedData = new SimpleXMLElement($data, null, false);


parse_recursive($parsedData);
}
?>
</body>
</html>
