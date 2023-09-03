<?php
	require('config/configuration.php');

    /**
     * Adds the header information required in the FDF format
     */
    function getFDFHeader() {
        $fdfHeader = "%FDF-1.2" . "\r\n";
        $fdfHeader .= "1 0 obj <</FDF<< /Fields[" . "\r\n";

        return $fdfHeader;
    }

    /**
     * Adds the footer information required in the FDF format
     */
    function getFDFFooter() {
        $fdfFooter = "] >> >>" . "\r\n";
        $fdfFooter .= "endobj" . "\r\n";
        $fdfFooter .= "trailer" . "\r\n";
        $fdfFooter .= "<</Root 1 0 R>>" . "\r\n";
        $fdfFooter .= "%%EOF";

        return $fdfFooter;
    }

    /**
     * Takes in a key/value pair and converts it to the FDF format
     */
    function convertKeyValueToFDF($key, $value) {
		global $debug;
		
		// Skip
		if(stringStartsWith($key, "SKIP")) {
			return;
		}

		$fdf = "";

		// Checkbox
		if(stringEndsWith($key, "_checkbox")) {
			$key = str_replace("_checkbox", "", $key);
			// Format:
			// << /V /On /T (Asian) >>
			
			// If the data was present in $_POST, that's because it was checked, so we can hardcode "/On" here
			$fdf .= "<< /V /On /T (" . $key . ") >> " . "\r\n";
		}

		// Radio Button
		else if(stringEndsWith($key, "_radio")) {
			$key = str_replace("_radio", "", $key);
			// Format:
			// << /V /Legally#20Responsible#20Adult /T (Relationship) >>

			// Spaces are encoded as #20
			$value = str_replace(" ", "#20", $value);
			
			$fdf .= "<< /V /" . $value . " /T (" . $key . ") >>" . "\r\n";
		}

		// SSN
		else if(stringEndsWith($key, "_ssn")) {
			$key = str_replace("_ssn", "", $key);

			// Format:
			// << /V (123) /T (SSN123)>>
			// << /V (45) /T (SSN45)>>
			// << /V (6789) /T (SSN6789)>>
			// Incoming: SSN; SSN_1, etc.
			// Outgoing: SSN123, SSN45, SSN6789; SSN123_1, SSN45_1, SSN6789_1, etc.

			// Grab the suffix, if we need one
			$ssnSuffix = "";
			if(strpos($key, "_") !== false) {
				$ssnSuffix = substr($key, strpos($key, "_"));
			}

			$fdf .= "<< /V (" . substr($value, 0, 3) . ") /T (" . "SSN123"  . $ssnSuffix . ") >>" . "\r\n";
			$fdf .= "<< /V (" . substr($value, 3, 2) . ") /T (" . "SSN45"   . $ssnSuffix . ") >>" . "\r\n";
			$fdf .= "<< /V (" . substr($value, 5)    . ") /T (" . "SSN6789" . $ssnSuffix . ") >>" . "\r\n";
		}

		// Date
		else if(stringEndsWith($key, "_date")) {
			$key = str_replace("_date", "", $key);
			// Format:
			// << /V (10) /T (Month) >>
			// << /V (25) /T (Day) >>
			// << /V (22) /T (Year) >> 

			// Incoming: Date; Date_1, etc.
			// Outgoing: Month, Day, Year; Month_1, Day_1, Year_1, etc.

			// Grab the suffix, if we need one
			if($debug) {
				echo "<p>Key before finding suffix: " . $key . "</p>";
			}
			if(strpos($key, "_") !== false) {
				$dateSuffix = substr($key, strpos($key, "_"));
				if($debug) {
					echo "<p>dateSuffix: " . $dateSuffix . "</p>";
				}
			} else {
				$dateSuffix = "";
			}

			// Split out the date into its component parts
			list($year, $month, $day) = explode("-", $value);

			$fdf .= "<< /V (" . $month . ") /T (Month" . $dateSuffix . ") >>" . "\r\n";
			$fdf .= "<< /V (" . $day   . ") /T (Day"   . $dateSuffix . ") >>" . "\r\n";
			$fdf .= "<< /V (" . $year  . ") /T (Year"  . $dateSuffix . ") >>" . "\r\n";
		}
		
		// Signature (Skip in FDF)
		else if(stringStartsWith($key, "hidden-signature")) {
			return "";
		}

		// Textbox, default
		else {
			// Format: 
			// << /V (Text) /T (Last) >> 

			// Backslashes are encoded as double backslashes
			$value = str_replace("\\", "\\\\", $value);
			// Parenthesis are encoded using \'s in front
			$value = str_replace("(", "\(", $value);
			$value = str_replace(")", "\)", $value);

			$fdf .= "<< /V (" . $value . ")" . " /T (" . $key . ") >>" . "\r\n";
		}
		return $fdf;
    }

	/**
	 * Adds today's date to every text field labeled TODAY
	 */
	function addTodaysDate() {
		return "<< /V (6/21/2022) /T (TODAY) >>" . "\r\n";
	}
?>