<?php
	// This is the PDF that we'll be filling output_add_rewrite_var
	if (stripos(PHP_OS, 'WIN') === 0) {
		$pdfLocation = ('C:\\Apache24\\subsidyapp\\config\\NJCK-Application-2022-English-pdftk.pdf');
	}
	else {
		$pdfLocation = ('config/NJCK-Application-2022-English-pdftk.pdf');
	}
	
	// Where we are storing the PDFs
	if (stripos(PHP_OS, 'WIN') === 0) {
		$outputLocation = 'C:\\Apache24\\subsidyapp\\output\\';
	} else {
		$outputLocation = 'output/';
	}

	// Location of the pdftk binary
	if (stripos(PHP_OS, 'WIN') === 0) {
		// On Windows, we specify the full path because it is not added to the SYSTEM user's PATH
		$pdftkBinary = '"C:\\Program Files (x86)\\PDFtk Server\\bin\\pdftk.exe"';
	} else {
		$pdftkBinary = 'pdftk';
	}

	// Sentinel value for removing spaces in field names
	$fieldNameSentinel = "$!$!";

	// If we're in debug mode, dump a lot of informaiton to the page
	$debug = FALSE;

	// Dump the FDF and POST data to files
	$dumpDataToFiles = TRUE;

	// Text presented to the user along with the final PDF
	$endingText = "<h2>Please review the content of your application and make corrections if needed. Print, sign, and return your application by uploading it using our website. <br/> On our website, hover over the Families Menu and select Document Upload.</h2>";
?>
