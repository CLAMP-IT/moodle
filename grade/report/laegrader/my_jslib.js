/* 
:->: fills all empty grades in the column with zeroes but does not automatically save :<-:
:->: Updated 6.15.12, Mark Hine :<-:
:->: Related changes made to laegrader/index.php :<-:
:->: echo '<div class="submit"> **<input type="button" value="Zero Fill" onClick="zerofill()";>** <input type="submit" value="'.s(get_string('update')).'" /></div>';
*/

function zerofill(_itemid) {
$("[title=Grade][rel=" + _itemid + "][value='']").attr("value","0.00");
alert("Zero Fill Complete! Make Sure You Save Your Changes!");
}

function clearoverrides(_itemid) {
	$("[title=Grade][rel=" + _itemid + "]").attr("overridden","0");
	alert("Overrides for this column cleared! Make Sure You Save Your Changes!");	
}