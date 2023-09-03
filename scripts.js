/**
 * Simple swap of visibility of a child element.
 * Used for checkboxes.
 */
function swapVisibility(elements) {
    Array.prototype.forEach.call(elements, function(element) {
        if(element.style.display == 'block') {
            element.style.display = 'none';
        } else {
            element.style.display = 'block';
        }
    });
}

/**
 * Swaps the visibility of an element depending on whether or not the text contains "Yes". If it does, make the children visible, if it does not, make them invisible.
 * Used for radio buttons.
 */
function swapVisibilityDependingOnText(elements, value) {
    // The value and the name may differ. This allows us to use the value as a selector to get the label, which would contain the name.
    // The name is likely always "Yes", where the value may be "Yes_6" or "1" depending on how the PDF is set up.
    var selector = "label[for='" + value + "']";
    var label = document.querySelector(selector);
    var text = label.innerHTML;


    if(text === "Yes") {
        Array.prototype.forEach.call(elements, function(element) {
            element.style.display = 'block';
        });
    } else {
        Array.prototype.forEach.call(elements, function(element) {
            element.style.display = 'none';
        });
    }
}

/**
 * Creates a new Signature Pad for each element whose class is "signature-pad"
 */
Array.from(document.getElementsByClassName("signature-pad")).forEach(
    function(element, index, array) {
        new SignaturePad(element), {
            backgroundColor: "rgba(255, 255, 255, 0)",
            penColor: "rgb(0, 0, 0)"
        }
    }
);

/**
 * Takes the data from the signature pad canvas and saves it as a base64 encoded PNG, and puts it into the hiddenSignature field,
 * which is then submitted normally when the form is
 * From: https://stackoverflow.com/a/5384732/774359
 */ 
function interceptFormSubmission(e) {
    // For every signature pad, grab the ID. Split up that ID to get the suffix. Use that suffix to find the hidden input element that will store the base64 encoded image
    Array.from(document.getElementsByClassName("signature-pad")).forEach(
        function(element, index, array) {
            var suffix = element.id.replace("signature-pad-", "");
            var idOfHiddenInput = "hidden-signature-" + suffix;
            var hiddenInput = document.getElementById(idOfHiddenInput);
            var base64Prefix = "data:image/png;base64";

            // If there was any base64 values previously in the hidden input, remove it before appending new values. This is for two reasons:
            // 1. We keep the page/x/y values in this input, which is why we append
            // 2. If you hit submit, and then back, the signature will already be in that hidden input. Hitting submit a second time would then re-append the base64, making it twice as long and no longer valid
            if(hiddenInput.value.indexOf(base64Prefix) > -1) {
                hiddenInput.value = hiddenInput.value.slice(0, hiddenInput.value.indexOf(base64Prefix));
            }
            
            // Grab the canvas of the signature pad and convert it to a base64-encoded PNG. Store that in the hidden element.
            hiddenInput.value += element.toDataURL("image/png");

            // This will stop the form from actually submitting. Useful when debugging.
            return false;
        }
    );
}

var form = document.getElementById("pdfForm");
if(form.attachEvent) {
    form.attachEvent("submit", interceptFormSubmission);
} else {
    form.addEventListener("submit", interceptFormSubmission);
}

/**
 * Clears out the signature pad so users can sign a second time
 */
function clearSignaturePad() {
    // Craft the ID of the signature pad to which this clear button is attached
    var signaturePadID = this.id.replace("clear", "signature-pad");
    var signaturePad = document.getElementById(signaturePadID);
    // Clear out the canvas
    const context = signaturePad.getContext("2d");
    context.clearRect(0, 0, signaturePad.width, signaturePad.height);
}

Array.from(document.getElementsByClassName("clear-signature")).forEach(
    function(element, index, array) {
        element.addEventListener("click", clearSignaturePad);
    }
)