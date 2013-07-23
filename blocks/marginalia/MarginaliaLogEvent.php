<?php

class MarginaliaLogEvent
{
	function MarginaliaLogEvent( )
	{
		$this->id = 0;
		$this->userid = 0;
		$this->service = '';
		$this->event = '';
		$this->ui = '';
		$this->object_type = 0;
		$this->object_id = 0;
		$this->modified = 0;
	}
}

class MarginaliaLogAnnotation extends Annotation
{
	function MarginaliaLogAnnotation( )
	{
		$this->eventid = 0;
		$this->userid = 0;
	}
}
