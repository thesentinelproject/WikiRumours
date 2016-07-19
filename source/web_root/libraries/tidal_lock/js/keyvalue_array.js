
	function keyValueToArray_TL(inputString, delimiter) {
		
		if (!delimiter) return false;

		var outputArray = new Object();

		if (inputString) {
			var pairs = inputString.split(delimiter);

			for (counter = 0; counter < pairs.length; counter++) {
				var keyValue = pairs[counter].split('=');
				if (keyValue[0] && keyValue[1]) outputArray[keyValue[0]] = keyValue[1];
			}
		}

		return outputArray;
		
	}
	
	function arrayToKeyValue_TL(inputArray, delimiter) {

		if (!delimiter) return false;
		
		outputString = '';

		if (inputArray) {
			for(var key in inputArray) {
				outputString += key + "=" + inputArray[key] + delimiter;
			}

			outputString += delimiter;
			outputString = outputString.replace(delimiter + delimiter, "");
		}
		
		return outputString;
		
	}
	
	function updateKeyValue_TL(inputString, key, value, delimiter) {
		
		if (!key || !delimiter) return false;

		var array = keyValueToArray_TL(inputString, delimiter);

		if (value != '') array[key] = value;
		else delete array[key];
		
		outputString = arrayToKeyValue_TL(array, delimiter);
		
		return outputString;
		
	}
