<?php

	class security_manager_TL {

		public $passwordScore = 0.00; // percentage
		public $passwordExplanationShort = null; // T for true, F for false, - for not applicable
		public $passwordExplanationLong = null; // plain English

		public function checkPasswordStrength($input, $minimumLength = 8, $alphanumeric = true, $nonalphanumeric = true) {

			$maxScore = 2;

			// check popular passwords
				$popularPasswords = array("password", "123456", "12345678", "1234", "qwerty", "12345", "dragon", "pussy", "baseball", "football", "letmein", "monkey", "696969", "abc123", "mustang", "michael", "shadow", "master", "jennifer", "111111", "2000", "jordan", "superman", "harley", "1234567", "fuckme", "hunter", "fuckyou", "trustno1", "ranger", "buster", "thomas", "tigger", "robert", "soccer", "fuck", "batman", "test", "pass", "killer", "hockey", "george", "charlie", "andrew", "michelle", "love", "sunshine", "jessica", "asshole", "6969", "pepper", "daniel", "access", "123456789", "654321", "joshua", "maggie", "starwars", "silver", "william", "dallas", "yankees", "123123", "ashley", "666666", "hello", "amanda", "orange", "biteme", "freedom", "computer", "sexy", "thunder", "nicole", "ginger", "heather", "hammer", "summer", "corvette", "taylor", "fucker", "austin", "1111", "merlin", "matthew", "121212", "golfer", "cheese", "princess", "martin", "chelsea", "patrick", "richard", "diamond", "yellow", "bigdog", "secret", "asdfgh", "sparky", "cowboy", "camaro", "anthony", "matrix", "falcon", "iloveyou", "bailey", "guitar", "jackson", "purple", "scooter", "phoenix", "aaaaaa", "morgan", "tigers", "porsche", "mickey", "maverick", "cookie", "nascar", "peanut", "justin", "131313", "money", "horny", "samantha", "panties", "steelers", "joseph", "snoopy", "boomer", "whatever", "iceman", "smokey", "gateway", "dakota", "cowboys", "eagles", "chicken", "dick", "black", "zxcvbn", "please", "andrea", "ferrari", "knight", "hardcore", "melissa", "compaq", "coffee", "booboo", "bitch", "johnny", "bulldog", "xxxxxx", "welcome", "james", "player", "ncc1701", "wizard", "scooby", "charles", "junior", "internet", "bigdick", "mike", "brandy", "tennis", "blowjob", "banana", "monster", "spider", "lakers", "miller", "rabbit", "enter", "mercedes", "brandon", "steven", "fender", "john", "yamaha", "diablo", "chris", "boston", "tiger", "marine", "chicago", "rangers", "gandalf", "winter", "bigtits", "barney", "edward", "raiders", "porn", "badboy", "blowme", "spanky", "bigdaddy", "johnson", "chester", "london", "midnight", "blue", "fishing", "0", "hannah", "slayer", "11111111", "rachel", "sexsex", "redsox", "thx1138", "asdf", "marlboro", "panther", "zxcvbnm", "arsenal", "oliver", "qazwsx", "mother", "victoria", "7777777", "jasper", "angel", "david", "winner", "crystal", "golden", "butthead", "viking", "jack", "iwantu", "shannon", "murphy", "angels", "prince", "cameron", "girls", "madison", "wilson", "carlos", "hooters", "willie", "startrek", "captain", "maddog", "jasmine", "butter", "booger", "angela", "golf", "lauren", "rocket", "tiffany", "theman", "dennis", "liverpoo", "flower", "forever", "green", "jackie", "muffin", "turtle", "sophie", "danielle", "redskins", "toyota", "jason", "sierra", "winston", "debbie", "giants", "packers", "newyork", "jeremy", "casper", "bubba", "112233", "sandra", "lovers", "mountain", "united", "cooper", "driver", "tucker", "helpme", "fucking", "pookie", "lucky", "maxwell", "8675309", "bear", "suckit", "gators", "5150", "222222", "shithead", "fuckoff", "jaguar", "monica", "fred", "happy", "hotdog", "tits", "gemini", "lover", "xxxxxxxx", "777777", "canada", "nathan", "victor", "florida", "88888888", "nicholas", "rosebud", "metallic", "doctor", "trouble", "success", "stupid", "tomcat", "warrior", "peaches", "apples", "fish", "qwertyui", "magic", "buddy", "dolphins", "rainbow", "gunner", "987654", "freddy", "alexis", "braves", "cock", "2112", "1212", "cocacola", "xavier", "dolphin", "testing", "bond007", "member", "calvin", "voodoo", "7777", "samson", "alex", "apollo", "fire", "tester", "walter", "beavis", "voyager", "peter", "porno", "bonnie", "rush2112", "beer", "apple", "scorpio", "jonathan", "skippy", "sydney", "scott", "red123", "power", "gordon", "travis", "beaver", "star", "jackass", "flyers", "boobs", "232323", "zzzzzz", "steve", "rebecca", "scorpion", "doggie", "legend", "ou812", "yankee", "blazer", "bill", "runner", "birdie", "bitches", "555555", "parker", "topgun", "asdfasdf", "heaven", "viper", "animal", "2222", "bigboy", "4444", "arthur", "baby", "private", "godzilla", "donald", "williams", "lifehack", "phantom", "dave", "rock", "august", "sammy", "cool", "brian", "platinum", "jake", "bronco", "paul", "mark", "frank", "heka6w2", "copper", "billy", "cumshot", "garfield", "willow", "cunt", "little", "carter", "slut", "albert", "69696969", "kitten", "super", "jordan23", "eagle1", "shelby", "america", "11111", "jessie", "house", "free", "123321", "chevy", "bullshit", "white", "broncos", "horney", "surfer", "nissan", "999999", "saturn", "airborne", "elephant", "marvin", "shit", "action", "adidas", "qwert", "kevin", "1313", "explorer", "walker", "police", "christin", "december", "benjamin", "wolf", "sweet", "therock", "king", "online", "dickhead", "brooklyn", "teresa", "cricket", "sharon", "dexter", "racing", "penis", "gregory", "0", "teens", "redwings", "dreams", "michigan", "hentai", "magnum", "87654321", "nothing", "donkey", "trinity", "digital", "333333", "stella", "cartman", "guinness", "123abc", "speedy", "buffalo");
				$popularPasswords = array_fill_keys($popularPasswords, true);
				if (
					$popularPasswords[strtolower($input)] ||
					$popularPasswords[str_replace('0', 'o', strtolower($input))] ||
					$popularPasswords[str_replace('1', 'i', strtolower($input))] ||
					$popularPasswords[str_replace('1', 'l', strtolower($input))] ||
					$popularPasswords[str_replace('5', 's', strtolower($input))]
				) $tooPopular = true;
				else $tooPopular = false;

			// check length
				if (strlen($input) < $minimumLength) $tooShort = true;
				else $tooShort = false;

			// check alphanumeric
				if ($alphanumeric) {
					$maxScore += 3;

					if (!preg_match('/[A-Za-z]/', $input)) $noLetters = true;
					else $noLetters = false;

					if (!$noLetters && (!preg_match('/[A-Z]/', $input) || !preg_match('/[a-z]/', $input))) $noMixedCase = true;
					else $noMixedCase = false;

					if (!preg_match('/[0-9]/', $input)) $noNumbers = true;
					else $noNumbers = false;

				}

			// check for special characters
				if ($nonalphanumeric) {
					$maxScore += 1;

					if (ctype_alnum($input)) $noSpecialChars = true;
					else $noSpecialChars = false;
				}

			// scoring
				$score = 0;
				if (!$tooPopular)			{ $this->passwordExplanationShort = "T"; $score++; }
				else						{ $this->passwordExplanationShort = "F"; }

				if (!$tooShort)				{ $this->passwordExplanationShort .= "T"; $score++; }
				else						{ $this->passwordExplanationShort .= "F"; }

				if ($alphanumeric) {
					if (!$noLetters)		{ $this->passwordExplanationShort .= "T"; $score++; }
					else					{ $this->passwordExplanationShort .= "F"; }

					if (!$noMixedCase)		{ $this->passwordExplanationShort .= "T"; $score++; }
					else					{ $this->passwordExplanationShort .= "F"; }

					if (!$noNumbers)		{ $this->passwordExplanationShort .= "T"; $score++; }
					else					{ $this->passwordExplanationShort .= "F"; }
				}
				else $this->passwordExplanationShort .= "---";

				if ($nonalphanumeric) {
					if (!$noSpecialChars)	{ $this->passwordExplanationShort .= "T"; $score++; }
					else					{ $this->passwordExplanationShort .= "F"; }
				}
				else $this->passwordExplanationShort .= "-";

				if ($score) $this->passwordScore = round($score / $maxScore, 2);
				$this->passwordExplanationLong = $this->expandPasswordExplanation($this->passwordExplanationShort);

				return $this->passwordScore;

		}

		public function expandPasswordExplanation($input) {

			if (!@$input) return false;

			if (substr($input, 0, 1) == 'F') $output = "Password isn't unique enough. ";
			if (substr($input, 1, 1) == 'F') $output = "Password is too short. ";
			if (substr($input, 2, 1) == 'F') $output = "Password doesn't contain letters. ";
			if (substr($input, 3, 1) == 'F') $output = "Password doesn't contain both uppercase and lowercase letters. ";
			if (substr($input, 4, 1) == 'F') $output = "Password doesn't contain numbers. ";
			if (substr($input, 5, 1) == 'F') $output = "Password doesn't contain non-alphanumeric characters. ";

			return trim($output);

		}
		
	}
 
/*
	Security Manager

	::	DESCRIPTION
	
		Function for validating passwords

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
