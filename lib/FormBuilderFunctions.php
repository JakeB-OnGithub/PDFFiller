<?php
	// Used to make sure that names of fields are always unique
	$uniqueID = 1;

    /**
	 * Shows the form field in HTML, depending on <input> type
	 */
	 function showFormField($field, $childName="") {
		global $fieldID;

		// Encode field name to remove spaces

		if(array_key_exists("Field Name", $field)) {
			$field["Field Name"] = encodeFieldName($field["Field Name"]);
		}

		$required = "";
		if(array_key_exists("Required", $field)) {
			$required = $field["Required"];
		}
		
		// If the field we're looking at IS a subfield, give it its proper name. Note that a single field can be both a child and a parent.
		$class = "";
		if($childName != "") {
			$class = "class='child " . $childName . "'";
		}

		echo "<div " . $class . ">";
		/** FIELD TYPES **/
		// Note: We append _ssn, _date, _radio, and _checkbox where applicable

		// Textbox
		if($field["Type"] == "Text") {
			// Format:
			// First Name*: <input type="text" name="First" required><br/>
			echo $field["Readable Name"];
			if($required != "") { echo "*"; }
			echo ": ";
			echo "<input type='text' name=" . $field["Field Name"] . " " . $required . ">";
		}
		
		// SSN
		if($field["Type"] == "SSN") {
			// Restrict to only numbers, using https://stackoverflow.com/a/65538050/774359
			// Format:
			// First Name*: <input type="text" name="SSN_ssn" maxlength="9" onkeypress='return event.charCode >= 48 && event.charCode <= 57' required><br/>
			echo $field["Readable Name"];
			if($required != "") { echo "*"; }
			echo ": ";
			echo "<input type='text'
					name=" . $field["Field Name"] . "_ssn" .
					" maxlength='9' " .
					"onkeypress='return event.charCode >= 48 && event.charCode <= 57' " .
					$required . ">";
		}

		// Checkbox
		else if($field["Type"] == "Checkbox") {
			// If the field we're looking at is a parent, generate an onclick line
			$onclick = "";
			if(array_key_exists("fields", $field)) {
				// Example: <input type="checkbox" onclick="swapVisibility(document.getElementById('child'));">
				$uniqueFieldName="child-" . ++$fieldID;
				$onclick = "onclick=\"swapVisibility(document.getElementsByClassName('" . $uniqueFieldName . "'));\"";
			}


			// Format: 
			// Asian*: <input type="checkbox" name="Asian_checkbox" required>
			echo $field["Readable Name"];
			if($required != "") { echo "*"; }
			echo ":";
			echo "<input type='checkbox' name='" . $field["Field Name"] . "_checkbox' " . $required ." ". $onclick . ">";
		}
		
		// Radio Button
		else if($field["Type"] == "Radio Button") {
			// If the field we're looking at is a parent, generate an onclick line
			$onclick = "";
			if(array_key_exists("fields", $field)) {
				// Example: <input type="radio" onclick="swapVisibilityDependingOnText(document.getElementById('child'), this.value);">
				$uniqueFieldName="child-" . ++$fieldID;
				$onclick = "onclick=\"swapVisibilityDependingOnText(document.getElementsByClassName('" . $uniqueFieldName . "'), this.value);\"";

			}


			// Format:
			// Gender*:
			// <input type="radio" name="Gender_radio" value="Male" onclick="swapVisibility(document.getElementById('child'));">
			// <label for="Male">Male</label><br/>
			echo $field["Readable Name"];
			if($required != "") { echo "*"; }
			echo ":<br/>";
			foreach($field["Options"] as $option) {
				// Under normal circumstances, the value and the display option should be the same.
				// If, however, $option is of the format "Yes [[1]]", it means that "Yes" shoudl be displayed and "1" should be the value.
				if(strpos($option, "[[") !== false) {
					$name = getNameFromDoubleBracket($option);
					$value = getValueFromDoubleBracket($option);
				} else {
					$name = $option;
					$value = $option;
				}

				// If the name of the field is "SKIP", that means that the value isn't used anywhere in the PDF. There's an issue with radio buttons that if
				// they're all named "SKIP_radio", checking any one of them will uncheck any other. Therefore, we need to make sure that we're using a unique
				// name for each one.
				if($field["Field Name"] == "SKIP") {
					$field["Field Name"] = "SKIP" . ++$GLOBALS['uniqueID'];
				}

				echo "<input type='radio' " . $required . " name='" . $field["Field Name"]. "_radio' value='" . $value . "' " . $onclick . ">";
				echo "<label for='" . $value . "'>" . $name . "</label><br/>";
			}
		}

		// Date
		else if ($field["Type"] == "Date") {
			// Format:
			// First date*: (<input type="date" name="First date_date" require">
			echo $field["Readable Name"];
			if($required != "") { echo "*"; }
			echo ":";
			echo "<input type='date' name=" . $field["Field Name"] . "_date " . $required . ">";
		}

		// Heading
		else if($field["Type"] == "Heading") {
			if($field["Tag"] == "None") {
				echo $field["Readable Name"];
			} else {
				echo "<" . $field["Tag"] . ">";
				echo $field["Readable Name"];
				echo "</" . $field["Tag"] . ">";
			}
			
		}

		// Signature
		else if($field["Type"] == "Signature") {
			echo $field["Readable Name"];
			// Add a canvas whose class is signature-pad in order to allow a drawable signature pad.
			// Also add a hidden input that contains the page # and X&Y coordinates to start out, and will contain the base-64 encoded signature on submission.
			?>
				<canvas id="signature-pad-<?php echo $field["Field Name"]; ?>" class="signature-pad" width=200 height=30></canvas><br/>
            	<input
					type='hidden'
					id='hidden-signature-<?php echo $field["Field Name"]; ?>'
					name='<?php echo "hidden-signature-" . $field["Field Name"]; ?>'
					value='<?php echo $field["Page"] . "|" . $field["X"] . "|" . $field["Y"] . "|"; ?>'>
				</input>
            	
            	<button id="clear-<?php echo $field["Field Name"]; ?>" class="clear-signature" type="button">Clear</button>
			<?php
		}

		// If this field has child fields, show them by recursively calling this function
		if(array_key_exists("fields", $field)) {
			foreach($field["fields"] as $field) {
				showFormField($field, $uniqueFieldName);
			}
		}
		
		echo "</div>";
	 }
?>