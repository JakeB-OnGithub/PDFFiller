<html>
	<head>
		<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<?php
			// Contains helper functions that make this file more readable
			require('lib/HelperFunctions.php');
			require('lib/FormBuilderFunctions.php');
			
			// Grab all of the section JSON files in the config folder
			$allSectionJSONFiles = glob('config/sections/*.json');
			
			// Start the form
			echo "<form id='pdfForm' action='formFiller.php' method='post'>";
			
			// Moved the Addendum file to the end. Still (somewhat) easier than hardcoding the sections...
			$addendumFile = $allSectionJSONFiles[0];
			unset($allSectionJSONFiles[0]);
			array_push($allSectionJSONFiles, $addendumFile);

			// Run through each section and build out the form
			foreach($allSectionJSONFiles as $sectionJSONFile) {
				echo "<h1>" . getFileNameFromPath($sectionJSONFile) . "</h1>";
				
				// This file contains all of the fields, their readable names, and their types. Read it in and parse it.
				$json = file_get_contents($sectionJSONFile);
				// Decode the JSON file contents into a standard PHP array
				$json_data = json_decode($json, true);
				
				// Run through each field, printing it as a form input
				foreach($json_data["fields"] as $field) {
					showFormField($field);
				}
			}
			
			echo "<input type='submit'>";
			echo "</form>";
		?>
	</body>
	<script src="scripts.js"></script>
</html>