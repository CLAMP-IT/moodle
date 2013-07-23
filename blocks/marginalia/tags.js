function keywordsOnload( )
{
//	var replaceButton = document.getElementById( 'replace' );

	window.keywordService = new RestKeywordService( serviceRoot + '/keywords.php', true );
	keywordService.init( annotationKeywords );
	refreshKeywords( );
	
	window.annotationService = new RestAnnotationService( serviceRoot + '/annotate.php', {
		csrfCookie: 'MoodleSessionTest' } );
	
	addEvent( '#replace input', 'change', _clearReplaceCount );
	addEvent( '#replace input', 'keypress', _keypressReplaceNote );
	addEvent( '#replace button', 'click', _replaceNotes );
}

function refreshKeywords( )
{
	var list = document.getElementById( 'keywords' );
	var items = domutil.childrenByTagClass( list, 'li' );
	for ( var i = 0;  i < items.length;  ++i )
		list.removeChild( items[ i ] );

	keywordService.keywords.sort( compareKeywords );
	var keywords = keywordService.keywords;
	var keywordDisplay = document.getElementById( 'keyword-display' );
	keywordDisplay.style.display = keywords.length ? 'block' : 'none';
	for ( var i = 0;  i < keywords.length;  ++i )
	{
		var keyword = keywords[ i ];
		list.appendChild( domutil.element( 'li', {
			content: domutil.element( 'a', {
				href: summaryRoot + '&q=' + encodeURIComponent( keyword.name ),
				content: keyword.name } )
			} ) );
	}
}

function compareKeywords( k1, k2 )
{
	if ( k1.name < k2.name )
		return -1;
	else if ( k1.name > k2.name )
		return 1;
	else
		return 0;
}

function _keypressReplaceNote( event )
{
	if ( event.keyCode == 13 )
	{
		event.stopPropagation( );
		_replaceNotes( );
		return false;
	}
	return true;
}

function _clearReplaceCount( event )
{
	var prompt = document.getElementById( 'replace-count-prompt' );
	prompt.style.display = 'none';
}

function _replaceNotes( event )
{
	var oldNote = document.getElementById( 'old-note' );
	var newNote = document.getElementById( 'new-note' );
	f = function( t ) {
		var prompt = document.getElementById( 'replace-count-prompt' );
		prompt.style.display = 'block';
		var count = document.getElementById( 'replace-count' );
		while ( count.firstChild )
			count.removeChild( count.firstChild );
		count.appendChild( document.createTextNode( t ) );
		keywordService.refresh( refreshKeywords );
	}
	annotationService.bulkUpdate( oldNote.value, newNote.value, f );	
}


