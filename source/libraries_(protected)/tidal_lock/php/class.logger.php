<?php

	class logger_TL {

		public $log = array(); // activity, time, milliseconds, variance
		
		public function logItInMemory($activity) {
			
			$numberOfLogEntries = count($this->log);
			$currentTimeInMilliseconds = round(microtime(true) * 1000);
			if ($numberOfLogEntries > 0) {
				$previousMilliseconds = $this->log[$numberOfLogEntries - 1]['milliseconds'];
				$variance = $currentTimeInMilliseconds - $previousMilliseconds;
			}
			
			$this->log[$numberOfLogEntries]['activity'] = $activity;
			$this->log[$numberOfLogEntries]['time'] = date('H:i:s');
			$this->log[$numberOfLogEntries]['milliseconds'] = $currentTimeInMilliseconds;
			$this->log[$numberOfLogEntries]['variance'] = @$variance;
			
		}
		
		public function logItInDb($activity, $id = null, $otherAttributes = null, $skipIfReported = false) {
	
			// check for errors
				if (!$activity) {
					errorManager_TL::addError("No activity specified.");
					return false;
				}
				
				if (!function_exists('retrieveFromDb') || !function_exists('updateDb') || !function_exists('insertIntoDb')) {
					errorManager_TL::addError("Unable to locate database model(s).");
					return false;
				}
			
			// check if activity has already been reported but not acknowledged
				if ($skipIfReported) {
					$previouslyReported = retrieveFromDb('logs', array('activity'=>$activity, 'resolved'=>'0'), null, null, null);
			        if (count($previouslyReported) > 0) return false;
			    }
	
			// create new log entry or update previous entry
				if ($id) {
					if (is_array($otherAttributes)) updateDb('logs', array('activity'=>$activity) + $otherAttributes, array('log_id'=>$id), null, null, null);
					else updateDb('logs', array('activity'=>$activity), array('log_id'=>$id), null, null, null);
				}
				else {
					if (is_array($otherAttributes)) $id = insertIntoDb('logs', array('activity'=>$activity, 'connected_on'=>date('Y-m-d H:i:s')) + $otherAttributes);
					else $id = insertIntoDb('logs', array('activity'=>$activity, 'connected_on'=>date('Y-m-d H:i:s')));
				}
	
			// add activity, error info & resolution
				return $id;
		 
		}
		
		public function retrieveLogFromMemory($delimiter = '|', $showMilliseconds = false) {
			
			$numberOfLogEntries = count($this->log);
			$output = '';
			
			for ($counter = 0; $counter < $numberOfLogEntries; $counter++) {
				$output .= $this->log[$counter]['time'] . " - " . $this->log[$counter]['activity'];
				if ($showMilliseconds) $output .= " (" . $this->log[$counter]['milliseconds'] . " / +" . $this->log[$counter]['variance'] . ")";
				if ($counter < $numberOfLogEntries - 1) $output .= $delimiter;
			}
			
			return $output;
			
		}
		
	}
	
/*	
	Logging

	::	DESCRIPTION
	
		Functions for logging user/system activity and errors

	::	DEPENDENT ON
	
		phpmailerWrapper_TL
	
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
