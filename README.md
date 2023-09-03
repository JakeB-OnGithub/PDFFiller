How to Use:
===========
- Take the original NJCK-Application-2022-English.pdf and run it through pdftk:
	.\pdftk.exe NJCK-Application-2022-English-Optimized.pdf output new.pdf
- To edit the fields, edit each section in config/sections/Section #.json

Installation Notes:
===================
- PDFTK must be installed on the server, using `apt install pdftk` or the equivalent for other package managers.
- Historical note: The original PDF library is from http://www.fpdf.org/ but does not support checkboxes in PDF forms. The currently-installed library is from https://github.com/codeshell/fpdm, which does support checkboxes. After working through this library, I later found that it didn't support radio buttons at all, so I switched to using PDFTK.

JSON:
=====
Basic format:
```
{
	"fields": [{
		"Readable Name": "First Name",
		"Field Name": "First",
		"Type": "Text",
		"Required": "Required"
	},
	{
		"Readable Name": "Gender",
		"Field Name": "Sex - 1",
		"Type": "Radio Button",
		"Options": [
			"Male",
			"Female"
		]
	},
	{
		"Readable Name": "What is your race?",
		"Type": "Heading",
		"Tag": "h2"
	},
	{
		"Readable Name": "First Name",
		"Field Name": "SKIP",
		"Type": "Checkbox"
	}
	]
}
```

Fields:

 - Readable Name: This is what shows up as the label to the input
   - Field Name: This is the name of the field within the PDF form
   - Type: Can be text, checkbox, radio, date, SSN, or signature
     - Date is a calendar picker, and is split up into day/month/year when dumping to the PDF
	 - SSN is similarly split into 3 sections when dumping to the PDF and is restricted to 9 numbers
   - The "Required" attribute can be skipped. It will only mark a field as required if the field is present and the value is not blank.
   - The Heading "Tag" field can be set to any HTML tag and will just be put into <$tag>heading</$tag>. To skip any tags, just set that field to "None"
   - Setting a "Field Name" to "SKIP" still shows it in the HTML form, but skips its output in the FDF file (and therefore the PDF).
     - This is useful for when the client asks a basic question ("Is there someone else in your house?") behind which is gated questions ("What is their name?"), but the basic question is just for hiding and has no PDF equivalent

Each field can have its own array of fields, to infinite depth, but the parent must be a checkbox:
```
{
	"fields": [{
		"Readable Name": "First Name",
		"Field Name": "First",
		"Type": "Checkbox",
		"fields": [{
			"Readable Name": "Last Name",
			"Field Name": "Last",
			"Type": "Text",
			"Required": "Required"
		}]
	}]
}
```

Radio button options can differ in their name and value with the use of double brackets:
```
{
		"Readable Name": "Do you work?",
		"Field Name": "Working",
		"Type": "Radio Button",
		"Options": [
			"Yes [[1]]",
			"No [[2]]"
		]
}
```
In this case, "Yes" and "No" will appear on the form, but the PDF uses "1" and "2" as the values to check off the boxes.

Signatures are handled with the following:
```
{
	"Readable Name": "Applicant Signature",
	"Field Name": "ApplicantSignature_Page4",
	"Type": "Signature",
	"Page": "4",
	"X": "85",
	"Y": "163"
}
```
Of note:

- The Readable Name still appears on the HTML form, but the Field Name is only used to create unique IDs
- You'll have to experiment with the X and Y coordinates to get it to work show up in the correct place, it's a huge pain
- The signature itself is saved as a base64-encoded PNG to a hidden input on form submission

Once the form is submitted, it goes through two passes:

1. The form data is dumped to an FDF file, which is then combined with the PDF using PDFTK. This form is flattened, because that's the only way step 2 works
2. The form above is read in and the signatures are applied on each relevant page using the FPDF library

Known Issues:

- You can't mark a child field as required
	- Say you have a checkbox like "Are you working?" and then a child textbox that says "Where?". You can't make "Where?" required, because if you don't have the checkbox checked, the child textbox won't appear. It'll still be on the form, and still be required - it just has display: none, so then you can't submit the form.
- Filling out child information and then unchecking the parent will still end up in the form
	- Say you have a checkbox like "Are you working?" and then a child textbox that says "Where?". If you check the parent checkbox, then fill out the "Where?" textbox, then uncheck the parent checkbox, the text you entered is still in the form, just hidden. Unchecking the parent doesn't clear the child.
- Checking off the "Other" radio button does not yield a textbox for it. Meaning, on the PDF, "Other" is a text field that you fill in yourself, but this project just doesn't handle that text field.

Useful commands:
	
  - To combine an FDF file with a PDF file and create a new PDF file:
    - `.\pdftk.exe .\NJCK-Application-2022-English-original.pdf fill_form file.fdf output newfile.pdf`
  - To extract an FDF file from a pre-filled PDF file:
	- `.\pdftk.exe .\NJCK-Application-2022-English-original.pdf generate_fdf output data.fdf`