<?php

	class logger_TL {

		public $log = array(); // activity, time, milliseconds, variance
		
		public function logItInMemory($activity) {
			
			$currentTimeInMilliseconds = round(microtime(true) * 1000);
			if (count($this->log) > 0) $variance = $currentTimeInMilliseconds - $this->log[count($this->log) - 1]['milliseconds'];
			
			$next = count($this->log);
			$this->log[$next]['activity'] = $activity;
			$this->log[$next]['time'] = date('H:i:s');
			$this->log[$next]['milliseconds'] = $currentTimeInMilliseconds;
			$this->log[$next]['variance'] = @$variance;
			
		}
		
		public function logItInDb($activity, $id = null, $relationships = null, $otherAttributes = null, $skipIfReported = false) {

			global $tl;
	
			// check for errors
				if (!$activity) {
					$tl->page['console'] .= __CLASS__ . "->" . __FUNCTION__ . ": No activity specified.\n";
					return false;
				}
				
			// check if activity has already been reported but not acknowledged
				if ($skipIfReported) {
					$previouslyReported = retrieveFromDb('logs', null, array('activity'=>$activity, 'is_resolved'=>'0'));
			        if (count($previouslyReported) > 0) return false;
			    }
			    
			// create new log entry or update previous entry
				if ($id) updateDb('logs', array('activity'=>$activity), array('log_id'=>$id), null, null, null, null, 1);
				else $id = insertIntoDb('logs', array('activity'=>$activity, 'connection_type'=>$tl->page['connection_type'], 'connected_on'=>date('Y-m-d H:i:s')));

				if (count($otherAttributes)) updateDb('logs', $otherAttributes, array('log_id'=>$id), null, null, null, null, 1);

			// add relationships
				if (@$relationships && is_array($relationships)) {
					foreach ($relationships as $relationship) {
						if (substr_count($relationship, '=') == 1) {
							$relationship = explode('=', $relationship);
							if ($relationship[0] && $relationship[1]) {
								deleteFromDbSingle('log_relationships', array('log_id'=>$id, 'relationship_name'=>$relationship[0], 'relationship_value'=>$relationship[1]));
								insertIntoDb('log_relationships', array('log_id'=>$id, 'relationship_name'=>$relationship[0], 'relationship_value'=>$relationship[1]));
							}
							else $tl->page['console'] .= "Malformed relationships attribute in logging query. ";
						}
						else $tl->page['console'] .= "Malformed relationships attribute in logging query. ";
					}
				}
	
			return $id;
		 
		}
		
		public function retrieveLogFromMemory($showSeconds = true, $showMilliseconds = false, $delimiter = "\n") {
			
			$numberOfLogEntries = count($this->log);
			$output = '';
			
			for ($counter = 0; $counter < $numberOfLogEntries; $counter++) {
				$output .= trim(($showSeconds ? $this->log[$counter]['time'] : false) . " | " . ($showMilliseconds ? $this->log[$counter]['milliseconds'] . " (+" . $this->log[$counter]['variance'] . ")" : false), "| ") . " - " . $this->log[$counter]['activity'];
				if ($counter < $numberOfLogEntries - 1) $output .= $delimiter;
			}
			
			return $output;
			
		}

		function dumpToConsole($input, $excludePrior = false) {
			
			global $tl;
			
			if ($excludePrior) $tl->page['console'] = $input;
			else $tl->page['console'] .= $input;
			
		}
		
	}
	
/*	
	Logging

	::	DESCRIPTION
	
		Functions for logging user/system activity and errors

	::	DEPENDENT ON
	
	::	VERSION HISTORY

	::	LICENSE
	
		Copyright (C) Timothy Quinn / Tidal Lock / Consolidated Biro
		
		Permission is hereby granted, free of charge, to any person
		obtaining a copy of this software and associated documentation
		files (the "Software"), to deal in the Software without
		restriction, including without limitation the rights to use,
		copy, modify, merge, publish, distribute, sublicense, and/or
		sell copies of the Software, and to permit persons to whom the
		Software is furnished to do so, subject to the following
		conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
		OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
		HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
		WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
		OTHER DEALINGS IN THE SOFTWARE.
*/
	
?>
