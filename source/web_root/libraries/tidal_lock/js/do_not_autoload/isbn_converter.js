/*
	* Copyright (C) 2010-2013 Timothy Quinn / Tidal Lock / Consolidated Biro
	* 
	* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
	* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
	* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	* 
*/

	function convertISBN_TL(isbnIn,hyphenate) {

		// Set default variables and cleanup ISBN
		isbnIn = isbnIn.replace(/[-\s]/g,&quot;&quot;).toUpperCase();
		var isbnnum = isbnIn;
		var isbn10exp = /^\d{9}[0-9X]$/;
		var isbn13exp = /^\d{13}$/;
		var isbnlen = isbnnum.length;
		var total = 0;

		// Preliminary validation

			if (isbnlen == 0) {
				alert(&quot;Please enter an ISBN to convert in the first box.&quot;);
				return false;
			}

			if (!(isbn10exp.test(isbnnum)) &amp;&amp; !(isbn13exp.test(isbnnum))) {
				if ((isbnlen != 10) &amp;&amp; (isbnlen != 13)) {
					alert(&quot;This ISBN is invalid.&quot; + &quot;\n&quot; + &quot;It contains &quot; + isbnlen + &quot; characters.&quot;);
				}
				else {
					alert(&quot;This ISBN is invalid.&quot; + &quot;\n&quot; + &quot;It contains invalid characters.&quot;);
				}
				return false;
			}

		// Convert a 10-digit ISBN

			if (isbnlen == 10) {

				// Test for 10-digit ISBNs: formulated number must be divisible by 11

				for (var x=0; x&lt;9; x++) {
					total = total+(isbnnum.charAt(x)*(10-x));
				}

				// check digit
					z = isbnnum.charAt(9);
					if (z == &quot;X&quot;) { z = 10; }

				// validate ISBN
					if ((total+z*1) % 11 != 0) {
						// modulo function gives remainder
							z = (11 - (total % 11)) % 11;
							if (z == 10) { z = &quot;X&quot;; }
							alert(&quot;This 10-digit ISBN is invalid.&quot; + &quot;\n&quot; + &quot;The check digit should be &quot; + z + &quot;.&quot;);
							return false;
					}
					else {
						// convert the 10-digit ISBN to a 13-digit ISBN
							isbnnum = &quot;978&quot; + isbnnum.substring(0,9);
							total = 0;
							for (var x=0; x&lt;12; x++) {
								if ((x % 2) == 0) { y = 1; }
								else { y = 3; }
								total = total+(isbnnum.charAt(x)*y);
							}
							z = (10 - (total % 10)) % 10;
					}
			}

		// Validate &amp; convert a 13-digit ISBN

			else {
				for (var x=0; x&lt;12; x++) {
					if ((x % 2) == 0) { y = 1; }
					else { y = 3; }
					total = total+(isbnnum.charAt(x)*y);
				}

				// check digit
					z = isbnnum.charAt(12);

				// validate ISBN
					if ((10 - (total % 10)) % 10 != z) {
						// modulo function gives remainder
							z = (10 - (total % 10)) % 10;
							alert(&quot;This 13-digit ISBN is invalid.&quot; + &quot;\n&quot; + &quot;The check digit should be &quot; + z + &quot;.&quot;);
							return false;
					}
					else {
						// convert the 13-digit ISBN to a 10-digit ISBN
							if ((isbnnum.substring(0,3) != &quot;978&quot;)) {
								alert(&quot;This 13-digit ISBN does not begin with \&quot;978\&quot;&quot; + &quot;\n&quot; + &quot;It cannot be converted to a 10-digit ISBN.&quot;);
								return false;
							}
							else {
								isbnnum = isbnnum.substring(3,12);
								total = 0;
								for (var x=0; x&lt;9; x++) {
									total = total+(isbnnum.charAt(x)*(10-x));
								}
								z = (11 - (total % 11)) % 11;
								if (z == 10) { z = &quot;X&quot;; }
							}
					}
		}

		if (hyphenate == true) {
			bAlert = true;
			form.isbn_in.value = hyphenate(form.isbn_in.value);
			bAlert = false;
			return(hyphenate(isbnnum+z));
		}
		else {
			return(isbnnum+z);
		}

	}

	function hyphenate(isbn) {

		var prefix;

		if (isbn.length == 13)	// for 13-digit ISBNs
		{
			prefix = isbn.substring(0,3) + &quot;-&quot;;
			isbn = isbn.substring(3,13);
		}
		else { prefix = ''; }

		var d = eval(isbn.substring(0,1));	// one digit
		var d2 = eval(isbn.substring(1,3));	// two digits
		var d4 = eval(isbn.substring(1,5));	// four digits
		var objRegExp = &quot;&quot;;

		switch(d) {
			case 0:
		    case 3:
		    case 4:
		/*
		0 = English-speaking areas
		3 = German-speaking areas
		4 = Japan
		*/
			switch(true) {
				case (d2 &lt; 20):
					objRegExp = /(\d)(\d{2})(\d{6})(\w)/;
				   break;
			   case (d2 &lt; 70):
					objRegExp = /(\d)(\d{3})(\d{5})(\w)/;
					break;
			   case (d2 &lt; 85):
					objRegExp = /(\d)(\d{4})(\d{4})(\w)/;
					break;
			   case (d2 &lt; 90):
					objRegExp = /(\d)(\d{5})(\d{3})(\w)/;
					break;
			   case (d2 &lt; 95):
					objRegExp = /(\d)(\d{6})(\d{2})(\w)/;
					break;
			   case (d2 &lt;= 99):
					objRegExp = /(\d)(\d{7})(\d)(\w)/;
					break;
			   default:
				   break;
			}
			break;

			case 1:
		/*
		1 = English-speaking areas
		*/
			switch(true) {
				case (d4 &lt; 1000):
					objRegExp = /(\d)(\d{2})(\d{6})(\w)/;
				   break;
			   case (d4 &lt; 4000):
					objRegExp = /(\d)(\d{3})(\d{5})(\w)/;
					break;
			   case (d4 &lt; 5500):
					objRegExp = /(\d)(\d{4})(\d{4})(\w)/;
					break;
			   case (d4 &lt; 8698):
					objRegExp = /(\d)(\d{5})(\d{3})(\w)/;
					break;
			   case (d4 &lt; 9990):
					objRegExp = /(\d)(\d{6})(\d{2})(\w)/;
					break;
			   case (d4 &lt;= 9999):
					objRegExp = /(\d)(\d{7})(\d)(\w)/;
					break;
			   default:
				   break;
			}
			break;

		    case 2:
		/*
		2 = French-speaking areas
		*/
			switch(true) {
				case (d2 &lt; 20):
					objRegExp = /(\d)(\d{2})(\d{6})(\w)/;
				   break;
			   case (d2 &lt; 70):
					objRegExp = /(\d)(\d{3})(\d{5})(\w)/;
					break;
			   case (d2 &lt; 84):
					objRegExp = /(\d)(\d{4})(\d{4})(\w)/;
					break;
			   case (d2 &lt; 90):
					objRegExp = /(\d)(\d{5})(\d{3})(\w)/;
					break;
			   case (d2 &lt; 95):
					objRegExp = /(\d)(\d{6})(\d{2})(\w)/;
					break;
			   case (d2 &lt;= 99):
					objRegExp = /(\d)(\d{7})(\d)(\w)/;
					break;
			   default:
				   break;
			}
			break;

		    case 9:
		/*
		90 = Dutch/Flemish-speaking
		*/
		  if (isbn.substring(1,2) == 0) {
			d2 = isbn.substring(2,4);
			switch(true) {
				case (d2 &lt; 20):
					objRegExp = /(\d{2})(\d{2})(\d{5})(\w)/;
				   break;
			   case (d2 &lt; 50):
					objRegExp = /(\d{2})(\d{3})(\d{4})(\w)/;
					break;
			   case (d2 &lt; 70):
					objRegExp = /(\d{2})(\d{4})(\d{3})(\w)/;
					break;
			   case (d2 &lt; 80):
					objRegExp = /(\d{2})(\d{5})(\d{2})(\w)/;
					break;
			   case (d2 &lt;= 81):
					objRegExp = /(\d{2})(\d{6})(\d)(\w)/;
					break;
			   default:
				break;
			}
		  }

		/*
		965 = Israel
		*/
		  if (isbn.substring(1,3) == 65) {
			d2 = isbn.substring(3,5);
			switch(true) {
				case (d2 &lt; 20):
					objRegExp = /(\d{3})(\d{2})(\d{4})(\w)/;
				   break;
			   case (d2 &lt; 70):
					objRegExp = /(\d{3})(\d{3})(\d{3})(\w)/;
					break;
			   case (d2 &lt; 90):
					objRegExp = /(\d{3})(\d{4})(\d{2})(\w)/;
					break;
			   case (d2 &lt;= 95):
					objRegExp = /(\d{3})(\d{5})(\d)(\w)/;
					break;
			   default:
				   break;
			}
		  }

		/*
		981 = Singapore
		*/
		  if (isbn.substring(1,3) == 81) {
			d2 = isbn.substring(3,5);
			switch(true) {
				case (d2 &lt; 20):
					objRegExp = /(\d{3})(\d{2})(\d{4})(\w)/;
				   break;
			   case (d2 &lt; 30):
					objRegExp = /(\d{3})(\d{3})(\d{3})(\w)/;
					break;
			   case (d2 &lt;= 40):
					objRegExp = /(\d{3})(\d{4})(\d{2})(\w)/;
					break;
			   default:
				   break;
			}
		  }
		  else { break; }

			   break;

			default:
				break;
		}

		if (objRegExp != &quot;&quot;) {
			  isbn = prefix + isbn.replace(objRegExp, &quot;$1-$2-$3-$4&quot;);
		}
		else {
			if (bAlert == true) { alert(&quot;Unable to hyphenate this ISBN!&quot;); }
			isbn = (prefix + isbn).replace(/[-]/g,&quot;&quot;);
		}
		return isbn;

	}

	/*
		Convert ISBN v1.0

		-- Description:
		Converts an ISBN10 into an ISBN13 and vice versa.

		-- Used in:
		Guernica Editions

		-- History:
		2011-03-01: Authored first version.
	*/
