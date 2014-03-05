<?php

	class operators_TL {

		public function firstTrue() {
			
			$values = func_get_args();
			
			foreach ($values as $counter => $value) {
				if ($value) return $value;
			}
			
		}
		
		public function firstTrueStrict() {
	
			$values = func_get_args();
			
			foreach ($values as $counter => $value) {
				if (!is_null($value)) return $value;
			}
			
		}

		public function howLongAgo($dateTime) {
			
			if (!$dateTime) {
				errorManager_TL::addError("No comparative date specified.");
				return false;
			}
			
			$currentTimestamp = time();
			$desiredTimestamp = strtotime($dateTime);
	
			// determine time difference
				$differenceInSeconds = $currentTimestamp - $desiredTimestamp;
				$differenceInMinutes = intval($differenceInSeconds / 60);
				$differenceInHours = intval($differenceInSeconds / 60 / 60);
				$differenceInDays = intval($differenceInSeconds / 60 / 60 / 24);
				$differenceInWeeks = intval($differenceInSeconds / 60 / 60 / 24 / 7);
	
			// format string if difference can be expressed in minutes, hours, days or weeks
				if ($differenceInMinutes < 60 && $differenceInMinutes > 0) return $differenceInMinutes . " minute(s) ago";
				if ($differenceInHours < 24 && $differenceInMinutes > 0) return $differenceInHours . " hour(s) ago";
				if ($differenceInDays < 7 && $differenceInMinutes > 0) return $differenceInDays . " day(s) ago";
				if ($differenceInWeeks < 4 && $differenceInMinutes > 0) return $differenceInWeeks . " week(s) ago";
	
			// format string if difference is greater than 3 weeks, and include time if specified in input string
				if (strtotime($dateTime) == strtotime(date('Y-m-d', strtotime($dateTime)))) return "on " . date('F j, Y', strtotime($dateTime));
				else return "on " . date('F j, Y, \a\t g:i A', strtotime($dateTime));
			
		}
		
	}
	
/*
	Operators

	::	DESCRIPTION
	
		Functions to compare values

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