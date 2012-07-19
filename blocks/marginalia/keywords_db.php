<?php

class annotation_keywords_db
{
	function list_keywords( $userid )
	{
		global $CFG, $DB;
		// A keyword is a note that occurs more than once
		$query =
			'SELECT a.note AS name, \'\' AS description'
			. ' FROM '.$CFG->prefix.AN_DBTABLE.' a'
			. ' JOIN ('
			. '  SELECT note, count(*) as m'
			. "  FROM {$CFG->prefix}".AN_DBTABLE
			. "  WHERE userid= :userid"
			. '  GROUP BY note) AS b'
			. ' ON a.note = b.note'
			. ' AND b.m > 1'
			. ' GROUP BY a.note'
			. ' ORDER BY a.note';
		$params = array( 'userid' => $userid );
		$keywordset = $DB->get_recordset_sql( $query, $params );
		$keywords = array( );
		$i = 0;
		foreach ( $keywordset as $r )
		{
			$keyword = new MarginaliaKeyword( );
			$keyword->name = $r->name;
			$keyword->description = $r->description;
			$keywords[ $i++ ] = $keyword;
		}
		return $keywords;
	}
}

?>
