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

		public function howLongAgo($dateTime, $currentDateTime = null) {

			global $console;
			
			if (!$dateTime) {
				$console .= __CLASS__ . "->" . __FUNCTION__ . ": No comparative date specified.\n";
				return false;
			}
			if (!$currentDateTime) $currentDateTime = date('Y-m-d H:i:s');

			$currentTimestamp = strtotime($currentDateTime);
			$desiredTimestamp = strtotime($dateTime);
	
			// determine time difference
				$differenceInSeconds = $currentTimestamp - $desiredTimestamp;
				if ($differenceInSeconds < 60 && $differenceInSeconds > 0) {
					if ($differenceInSeconds == 1) return $differenceInSeconds . " second ago";
					else return $differenceInSeconds . " seconds ago";
				}

				$differenceInMinutes = round($differenceInSeconds / 60);
				if ($differenceInMinutes < 60 && $differenceInMinutes > 0) {
					if ($differenceInMinutes == 1) return $differenceInMinutes . " minute ago";
					else return $differenceInMinutes . " minutes ago";
				}

				$differenceInHours = round($differenceInSeconds / 60 / 60);
				if ($differenceInHours < 24 && $differenceInMinutes > 0) {
					if ($differenceInHours == 1) return $differenceInHours . " hour ago";
					else return $differenceInHours . " hours ago";
				}

				$differenceInDays = round($differenceInSeconds / 60 / 60 / 24);
				if ($differenceInDays < 7 && $differenceInMinutes > 0) {
					if ($differenceInDays == 1) return $differenceInDays . " day ago";
					else return $differenceInDays . " days ago";
				}

				$differenceInWeeks = round($differenceInSeconds / 60 / 60 / 24 / 7);
				if ($differenceInWeeks < 4 && $differenceInMinutes > 0) {
					if ($differenceInWeeks == 1) return $differenceInWeeks . " week ago";
					else return $differenceInWeeks . " weeks ago";
				}

				$differenceInMonths = round($differenceInSeconds / 60 / 60 / 24 / 30);
				if ($differenceInMonths < 12 && $differenceInMinutes > 0) {
					if ($differenceInMonths == 1) return $differenceInMonths . " month ago";
					else return $differenceInMonths . " months ago";
				}

			// format string if difference is greater than 3 weeks, and include time if specified in input string
				if (strtotime($dateTime) == strtotime(date('Y-m-d', strtotime($dateTime)))) return "on " . date('F j, Y', strtotime($dateTime));
				else return "on " . date('F j, Y, \a\t g:i A', strtotime($dateTime));
			
		}
		
		public function inHowLong($currentDateTime, $dateTime) {

			global $console;
			
			if (!$dateTime) {
				$console .= __CLASS__ . "->" . __FUNCTION__ . ": No comparative date specified.\n";
				return false;
			}
			if (!$currentDateTime) $currentDateTime = date('Y-m-d H:i:s');

			$currentTimestamp = strtotime($currentDateTime);
			$desiredTimestamp = strtotime($dateTime);
	
			// determine time difference
				$differenceInSeconds = $desiredTimestamp - $currentTimestamp;
				if ($differenceInSeconds < 60 && $differenceInSeconds > 0) {
					if ($differenceInSeconds == 1) return "in " . $differenceInSeconds . " second";
					else return "in " . $differenceInSeconds . " seconds";
				}

				$differenceInMinutes = round($differenceInSeconds / 60);
				if ($differenceInMinutes < 60 && $differenceInMinutes > 0) {
					if ($differenceInMinutes == 1) return "in " . $differenceInMinutes . " minute";
					else return "in " . $differenceInMinutes . " minutes";
				}

				$differenceInHours = round($differenceInSeconds / 60 / 60);
				if ($differenceInHours < 24 && $differenceInMinutes > 0) {
					if ($differenceInHours == 1) return "in " . $differenceInHours . " hour";
					else return "in " . $differenceInHours . " hours";
				}

				$differenceInDays = round($differenceInSeconds / 60 / 60 / 24);
				if ($differenceInDays < 7 && $differenceInMinutes > 0) {
					if ($differenceInDays == 1) return "in " . $differenceInDays . " day";
					else return "in " . $differenceInDays . " days";
				}

				$differenceInWeeks = round($differenceInSeconds / 60 / 60 / 24 / 7);
				if ($differenceInWeeks < 4 && $differenceInMinutes > 0) {
					if ($differenceInWeeks == 1) return "in " . $differenceInWeeks . " week";
					else return "in " . $differenceInWeeks . " weeks";
				}

				$differenceInMonths = round($differenceInSeconds / 60 / 60 / 24 / 30);
				if ($differenceInMonths < 12 && $differenceInMinutes > 0) {
					if ($differenceInMonths == 1) return "in " . $differenceInMonths . " month";
					else return "in " . $differenceInMonths . " months";
				}

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