<html>
	<head><link rel="stylesheet" href="style.css"></head>
	<body>
		<div id="content">

<?php
	require('lib/HelperFunctions.php');
	require('lib/FormFillerFunctions.php');
	require('lib/PDFSignatures.php');
	require('config/configuration.php');

	/** ERROR HANDLING **/

	// If they haven't sent any form information, just stop rendering the page
	if(empty($_POST)) {
		echo "<p>You've visited this page in error.</p>";
		exit();
	}

	if($debug) {
		echo "<h1>POST Data</h1>";
		echo "<div style='border-style: solid;'>";
		prettyPrint($_POST);
		echo "</div>";
	}

	// Generate filenames based on timestamp
	$timestamp = time();
	$outputPOST = $outputLocation . $timestamp . ".txt";
	$outputFDF = $outputLocation . $timestamp . ".fdf";
	$outputPDF = $outputLocation . $timestamp . ".pdf";
	
	if($dumpDataToFiles) {
		$post_dump = print_r($_POST, true);
		file_put_contents($outputPOST, $post_dump);
	}

	/** PROCESSING **/
	$fdf = "";
	foreach($_POST as $key => $value) {
		// If the user filled nothing in the field
		if($value == "") {
			continue;
		}

		// Remove sentinel values that encode the field names for $_POST
		$key = decodeFieldName($key);

		// Add the FDF information to the running variable
		$fdf .= convertKeyValueToFDF($key, $value);
	}

	// Add today's date to every text field named TODAY
	$fdf .= addTodaysDate();

	// Add the header and footer sections to the data
	$fdf = getFDFHeader() . $fdf . getFDFFooter();

	/** OUTPUT **/
	if($debug) {
		echo "<h1>FDF Data</h1>";
		echo "<div style='border-style: solid;'>";
		prettyPrint(htmlspecialchars($fdf));
		echo "</div>";
	}

	// Immediately output the fact that we're generating so we don't wait for the entire script to finish
	echo "<p>Generating PDF file...</p>";
	while (@ob_end_flush());
	flush();

	// Dump FDF data to file
	file_put_contents($outputFDF, $fdf);

	// Generate the PDF with the FDF data
	/**
	 * Generate the PDF
	 * 
	 * Linux:
	 * 		exec("pdftk config/NJCK-Application-2022-English-pdftk.pdf fill_form config/file.fdf output form_with_data.pdf");
	 * Windows:
	 * 		exec("\"C:\\path\\pdftk.exe\" C:\\Apache\\NJCK.pdf fill_form C:\\path\\config\\file.fdf output C:\\path\\output.pdf");
	 */
	$output = null;
	$retval = null;
	$cmd = $pdftkBinary . " " . $pdfLocation . " fill_form " . $outputFDF . " output " . $outputPDF . " flatten";
	exec($cmd, $outut, $retval);

	if($debug) {
		echo "<p>Command: " . $cmd . "</p>";
		echo "<p>Output: ";
		prettyPrint($output);
		echo "</p>";
		echo "<p>Retval: " . $retval . "</p>";
		echo "<p>The PDF is stored in: " . $outputPDF . "</p>";
	}

	// Add signatures to the PDF
	$outputPDF = addSignaturesToPDF($outputPDF, $outputLocation, $_POST, $debug);
	if($debug) {
		echo "<p>The signed PDF is stored in: " . $outputPDF . "</p>";
	}


	echo $endingText;
	echo "<p><br/><a href='/'>Home</a></p>";

	if (stripos(PHP_OS, 'WIN') === 0) {
		// At this point, $outputPDF is the filepath (including C:\) and it needs to be the relative path on the server
		$outputPDF = "/output/" . basename($outputPDF);
	}
	echo "<iframe src='" . $outputPDF . "' width='100%' height='100%'></iframe>";
?>
		</div>
	</body>
</html>