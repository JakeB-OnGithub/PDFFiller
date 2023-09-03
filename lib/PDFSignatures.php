<?php
    require('lib/FPDF/fpdf.php');
    require_once('lib/FPDI-2.3.7/autoload.php');
    use setasign\Fpdi\Fpdi;
    require('config/configuration.php');
    
    function addSignaturesToPDF($unsignedPDF, $outputLocation, $postData, $debug) {
        // Initiate FPDI
        $pdf = new Fpdi();
        
        // Read in the existing PDF and grab the page count
        $pageCount = $pdf->setSourceFile($unsignedPDF);

        // Reduce $_POST to just the signature files
        $signaturesFromPost = [];
        foreach($_POST as $key => $value) {
            if(stringStartsWith($key, "hidden-signature")) {
                $exploded = explode("|", $value);
                
                $signature = [];
                $signature["pageNumber"] = $exploded[0];
                $signature["xCoordinate"] = $exploded[1];
                $signature["yCoordinate"] = $exploded[2];
                $signature["base64"] = $exploded[3];
                
                $signaturesFromPost[$key] = $signature;
            }
        }

        // This could probably done more efficiently, but since there are only 4 signatures in the file, this is good enough
        // Iterate over the pages of the PDF and add the signature on the correct page
        // From: https://stackoverflow.com/a/25914022/774359
        for($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $tplIdx = $pdf->importPage($pageNumber);
            $pdf->AddPage();
            $pdf->useTemplate($tplIdx, 0, 0);
    
            // Iterate over the signaturesFromPost. If we find one whose page # is the same as the page we're on, add that signature
            foreach($signaturesFromPost as $signature) {
                if($signature["pageNumber"] == $pageNumber) {
                    if($debug) {
                        echo "<p>Adding signature on page #" . $pageNumber . ", X=" . $signature["xCoordinate"] . ", Y=" . $signature["yCoordinate"] . ", signature name: " . $key . "</p>";
                    }
                    
                    $img = explode(',', $signature["base64"], 2)[1];
                    $pic = 'data://text/plain;base64,'. $img;
                    $pdf->Image($pic, $signature["xCoordinate"], $signature["yCoordinate"], 0, 0,'png');
                }
            }
        }

        // Output the PDF
        $outputPDF = $outputLocation . basename($unsignedPDF, ".pdf") . "_signed.pdf";
        $pdf->Output('F', $outputPDF);
        return $outputPDF;
    }
?>