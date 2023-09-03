<?php
	require('config/configuration.php');
	
	// Used for parent/child relationships
	$fieldID = 0;

	/**
	 * Generate a filename based on the date/time and the submitter's name (if available)
	 */
	function getPDFFilename() {
		// Need access to the variable from outside the function scope
		global $outputLocation;
		
		// Grab the submitter's name if that's available
		$submitterName = "";
		if(!empty($_POST["First"]) && !empty($_POST["Last"])) {
			$submitterName = "_" . $_POST["Last"] . "_" . $_POST["First"];
		}
		
		// Grab today's date and time in a string format
		$now = DateTime::createFromFormat('U.u', microtime(true));
		$dateTime = $now->format('Y-m-d_H-i-s.u_A');
		//$dateTime = date('Y-m-d_H-i-s.u_A');
		
		// Create the filename and sanitize it. https://stackoverflow.com/a/2021729
		$filename = $dateTime . $submitterName . ".pdf";
		$filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
		$filename = mb_ereg_replace("([\.]{2,})", '', $filename);
		
		return $outputLocation . $filename;
	}

	/**
	 * Sometimes the names in the PDF fields contain spaces. For ease of use, we want to be able to copy those field names directly into fields.json and not worry about what they contain.
	 * We then use those field names for the HTML `name` attribute. However, that attribute renders improperly in some browsers if there's a space, setting the name to only what is before
	 * the space. Therefore, we encode the name by replacing spaces with a sentinel value that wouldn't occur in the real field names. Later, we decode them by doing the reverse.
	 */
	function encodeFieldName($field) {
		global $fieldNameSentinel;
		
		return str_replace(" ", $fieldNameSentinel, $field);
	}

	/**
	 * Put spaces back instead of the sentinels
	 */
	function decodeFieldName($field) {
		global $fieldNameSentinel;
		
		return str_replace($fieldNameSentinel, " ", $field);
	}
	
	/**
	 * Takes in a file path and returns the name of the file, without the path and without the extension
	 */
	function getFileNameFromPath($filePath) {
		return substr(basename($filePath), 0, strpos(basename($filePath),'.'));
	}
	 
	 /**
	  * Prints the passed-in variable nicely with <pre> tags
	  */
	 function prettyPrint($variable) {
		 echo "<pre>";
		 print_r($variable);
		 echo "</pre>";
	 }

	 /**
	  * Creates an easily-seen message and skips a line afterwards. Useful when debugging, and can also be used for CTRL+F to remove all debug messages later
	  */
	 function debug($string) {
		echo "<strong>" . $string . "</strong>" . "<br/>";
	 }

	 /**
	  * Simple "ends with" function, because PHP only included an endsWith() in 8.0
	  * From: https://www.tutorialkart.com/php/php-check-if-string-ends-with-substring/
	  */
	 function stringEndsWith($string, $endsWith) {
		if(substr_compare($string, $endsWith, -strlen($endsWith)) === 0) {
			return true;
		} else {
			return false;
		}
	 }

	 /**
	  * Simple "starts with" function, because PHP only included a startsWith() in 8.0
	  * From: https://stackoverflow.com/a/2790919/774359
	  */
	 function stringStartsWith($string, $startsWith) {
		if(substr($string, 0, strlen($startsWith)) === $startsWith) {
			return true;
		} else {
			return false;
		}
	 }

	 /**
	  * Sometimes, the display option and the actual value differ. For example, some questions are yes/no but the actual options in the PDF are 1/2. Therefore, we set the option as "Yes [[1]]".
	  * This function would return the "1" within the brackets.
	  */
	  function getValueFromDoubleBracket($string) {
		if(strpos($string, "[[") == false) {
			return $string;
		}
		// Get the position of the double bracket
		$positionOfOpenDoubleBracket = strpos($string, "[[") + 2;
		$positionOfEndingDoubleBracket = strpos($string, "]]") ;	
		$value = slice($string, $positionOfOpenDoubleBracket, $positionOfEndingDoubleBracket);

		return $value;
	  }

	  /**
	  * Sometimes, the display option and the actual value differ. For example, some questions are yes/no but the actual options in the PDF are 1/2. Therefore, we set the option as "Yes [[1]]".
	  * This function would return the "Yes" within the brackets.
	  */
	  function getNameFromDoubleBracket($string) {
		if(strpos($string, "]]") == false) {
			return $string;
		}
		$positionOfDoubleBracket = strpos($string, "[[") - 1;
		$name = substr($string, 0, $positionOfDoubleBracket);
		return $name;
	  }

	  /**
	   * An easier method of string slicing, just provide the beginning and ending indices
	   */
	  function slice($string, $beginIndex, $endIndex) {
		return substr($string, $beginIndex, $endIndex - $beginIndex);
	  }
?>