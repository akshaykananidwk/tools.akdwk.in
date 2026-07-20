<?php
/** KRISHNA TOOLS — English guide + FAQ content (image & PDF tools). Used when the UI language is English and for FAQPage JSON-LD. */
return [
  'jpg-to-png' => [
    'guide' => [
      'Upload your JPG file or drag and drop it into the box',
      'The tool converts your image to PNG right in your browser',
      'Preview the transparent-ready PNG result on screen',
      'Click Download to save the new PNG file',
    ],
    'faq' => [
      ['Is my file uploaded to a server?', 'No. The conversion runs entirely in your browser, so your image never leaves your device.'],
      ['Does converting JPG to PNG improve quality?', 'It will not add lost detail, but PNG is lossless, so no further quality is lost after conversion.'],
      ['Is this tool free to use?', 'Yes. The free plan allows 3 conversions per day, and premium removes all daily limits.'],
    ],
  ],
  'png-to-jpg' => [
    'guide' => [
      'Upload the PNG image you want to convert',
      'The tool flattens transparency and creates a compact JPG in your browser',
      'Preview the smaller JPG output',
      'Download the finished JPG file',
    ],
    'faq' => [
      ['Why is the JPG smaller than my PNG?', 'JPG uses compression and drops transparency, which usually produces a much smaller file than PNG.'],
      ['What happens to transparent areas?', 'Transparent pixels are filled with a solid background, most often white, because JPG cannot store transparency.'],
      ['Are my images kept private?', 'Yes. Processing happens locally in your browser and nothing is stored on our servers.'],
    ],
  ],
  'webp-to-jpg' => [
    'guide' => [
      'Upload your WEBP image',
      'The tool decodes and converts it to JPG in your browser',
      'Preview the JPG result',
      'Download the converted JPG file',
    ],
    'faq' => [
      ['Why convert WEBP to JPG?', 'JPG is supported almost everywhere, so converting helps when an app or website does not accept WEBP.'],
      ['Will the image lose quality?', 'JPG is lossy, so there may be a slight change, but at normal quality settings the difference is hard to notice.'],
      ['Do I need to install anything?', 'No. The tool works directly in your browser with no software or plugins required.'],
    ],
  ],
  'jpg-to-webp' => [
    'guide' => [
      'Upload the JPG image you want to modernise',
      'The tool encodes it to the efficient WEBP format in your browser',
      'Preview the WEBP result and its smaller size',
      'Download the new WEBP file',
    ],
    'faq' => [
      ['Why use WEBP instead of JPG?', 'WEBP usually produces smaller files at similar quality, which helps websites load faster.'],
      ['Is WEBP supported by all browsers?', 'All modern browsers support WEBP, though very old software may still prefer JPG or PNG.'],
      ['Is there a usage limit?', 'The free plan allows 3 conversions per day, while premium unlocks unlimited use.'],
    ],
  ],
  'heic-to-jpg' => [
    'guide' => [
      'Upload the HEIC photo from your iPhone',
      'The tool decodes the HEIC image and converts it to JPG',
      'Preview the standard JPG version',
      'Download the JPG so any device can open it',
    ],
    'faq' => [
      ['Why will not my HEIC photo open on Windows?', 'HEIC is an Apple format that many devices do not support, so converting to JPG makes the photo universally readable.'],
      ['Are my photos safe?', 'Yes. HEIC files are processed in your browser and are never uploaded to a server.'],
      ['Does conversion keep the photo resolution?', 'Yes. The JPG keeps the original pixel dimensions of your HEIC photo.'],
    ],
  ],
  'image-to-pdf' => [
    'guide' => [
      'Upload one or more images in the order you want them',
      'Arrange the pages and choose page size if needed',
      'The tool builds a single PDF in your browser',
      'Download your combined PDF document',
    ],
    'faq' => [
      ['Can I combine several images into one PDF?', 'Yes. Add as many images as you like and they become sequential pages in a single PDF.'],
      ['Are my images uploaded anywhere?', 'No. The PDF is created locally in your browser, so your images stay private.'],
      ['Can I change the page order?', 'Yes. You can reorder the images before creating the PDF so the pages appear exactly as you want.'],
    ],
  ],
  'pdf-to-jpg' => [
    'guide' => [
      'Upload the PDF you want to convert',
      'The tool renders each page as an image',
      'Preview the JPG version of every page',
      'Download the pages individually or as a set',
    ],
    'faq' => [
      ['Does every PDF page become a separate JPG?', 'Yes. Each page is rendered into its own JPG image at a clear, readable resolution.'],
      ['Is my PDF kept private?', 'Yes. The PDF is processed in your browser and never sent to a server.'],
      ['Can I convert a specific page only?', 'Yes. You can select which pages to export instead of converting the whole document.'],
    ],
  ],
  'image-compressor' => [
    'guide' => [
      'Upload the image you want to shrink',
      'Enter a target size in KB or choose a quality level',
      'The tool compresses the image in your browser',
      'Download the smaller optimised image',
    ],
    'faq' => [
      ['Can I compress to an exact KB size?', 'Yes. Set a target size and the tool adjusts quality to reach it as closely as possible.'],
      ['Will compression ruin my image?', 'Light compression keeps images looking sharp, while heavier settings trade some detail for a smaller file.'],
      ['Is my image uploaded to a server?', 'No. Compression runs locally in your browser, so your image stays on your device.'],
    ],
  ],
  'image-resizer' => [
    'guide' => [
      'Upload the image you want to resize',
      'Enter new dimensions in pixels or percent, or pick a preset',
      'The tool scales the image while keeping proportions if locked',
      'Download the resized image',
    ],
    'faq' => [
      ['Can I keep the original aspect ratio?', 'Yes. Lock the ratio and the height adjusts automatically when you change the width.'],
      ['Will enlarging an image make it blurry?', 'Increasing size beyond the original resolution can soften details, since new pixels are estimated.'],
      ['Does resizing happen privately?', 'Yes. Images are resized in your browser and are not uploaded anywhere.'],
    ],
  ],
  'image-cropper' => [
    'guide' => [
      'Upload the image you want to crop',
      'Drag the crop box or pick an aspect ratio preset',
      'Adjust the selection until it frames the subject',
      'Download the cropped image',
    ],
    'faq' => [
      ['Can I crop to a fixed ratio like 1:1?', 'Yes. Choose a preset such as square, 4:3, or 16:9 to keep a precise ratio.'],
      ['Does cropping reduce quality?', 'No. Cropping only removes areas outside the selection and does not compress the remaining pixels.'],
      ['Is my photo stored online?', 'No. Cropping is done in your browser, so your photo never leaves your device.'],
    ],
  ],
  'rotate-flip' => [
    'guide' => [
      'Upload the image you want to adjust',
      'Rotate left or right, or flip horizontally or vertically',
      'Preview the corrected orientation',
      'Download the rotated or flipped image',
    ],
    'faq' => [
      ['Can I rotate by exactly 90 degrees?', 'Yes. Use the rotate buttons to turn the image in clean 90 degree steps.'],
      ['What is the difference between rotate and flip?', 'Rotate turns the image around its centre, while flip mirrors it along the horizontal or vertical axis.'],
      ['Is this tool free?', 'Yes. The free plan covers 3 uses per day, and premium removes the daily limit.'],
    ],
  ],
  'bulk-image-converter' => [
    'guide' => [
      'Upload many images at once',
      'Choose the target format for all of them',
      'The tool converts every image in your browser',
      'Download all results together as a ZIP file',
    ],
    'faq' => [
      ['How many images can I convert at once?', 'You can add a large batch and convert them together, with premium allowing bigger batches.'],
      ['Will all images use the same format?', 'Yes. The whole batch is converted to the single output format you select.'],
      ['Are my files uploaded to a server?', 'No. Every image is converted locally in your browser before being packed into the ZIP.'],
    ],
  ],
  'passport-photo' => [
    'guide' => [
      'Upload a clear front-facing photo',
      'Align the face inside the 35x45mm guide',
      'The tool creates a passport photo and a printable 4x6 sheet',
      'Download the single photo or the full sheet',
    ],
    'faq' => [
      ['What size passport photo does this make?', 'It produces a standard 35x45mm photo and arranges copies on a 4x6 inch sheet for printing.'],
      ['Is my photo kept private?', 'Yes. The photo is processed in your browser and is never uploaded to a server.'],
      ['Can I print the sheet at a photo shop?', 'Yes. The 4x6 sheet is sized for standard photo printing at home or at a shop.'],
    ],
  ],
  'signature-cleaner' => [
    'guide' => [
      'Upload a photo or scan of your signature',
      'The tool removes the white background automatically',
      'Preview the clean transparent signature',
      'Download the result compressed to about 20KB',
    ],
    'faq' => [
      ['Will the white background be fully removed?', 'Yes. The tool detects the light background and makes it transparent so the signature stands alone.'],
      ['Why is the output around 20KB?', 'Many forms require small signature files, so the tool compresses the result to meet common upload limits.'],
      ['Is my signature stored anywhere?', 'No. Cleaning happens in your browser, so your signature stays private on your device.'],
    ],
  ],
  'photo-kb-resizer' => [
    'guide' => [
      'Upload the photo you need to shrink',
      'Choose a target of 20, 50, or 100 KB',
      'The tool adjusts the photo to hit that exact size',
      'Download the resized photo ready for the form',
    ],
    'faq' => [
      ['Why do exam forms need an exact KB size?', 'Many exam and government portals reject files that are too large, so a precise size ensures the upload is accepted.'],
      ['Does hitting a small size lower quality?', 'Reaching a very small target reduces quality slightly, but the tool keeps the photo as clear as possible.'],
      ['Is my photo uploaded to a server?', 'No. The photo is resized in your browser and never leaves your device.'],
    ],
  ],
  'watermark-adder' => [
    'guide' => [
      'Upload the image you want to protect',
      'Add text or upload a logo watermark',
      'Set position, size, and transparency',
      'Download the watermarked image',
    ],
    'faq' => [
      ['Can I use my own logo as a watermark?', 'Yes. Upload a logo image and place it anywhere on your photo with adjustable opacity.'],
      ['Can I control how visible the watermark is?', 'Yes. Change the transparency so the mark is subtle or bold depending on your needs.'],
      ['Are my images processed privately?', 'Yes. Watermarking is done in your browser, so your images are never uploaded.'],
    ],
  ],
  'image-to-base64' => [
    'guide' => [
      'Upload an image to encode, or paste a Base64 string to decode',
      'The tool converts between image and Base64 in your browser',
      'Copy the Base64 text or preview the decoded image',
      'Download the image or copy the code as needed',
    ],
    'faq' => [
      ['What is Base64 used for?', 'Base64 lets you embed an image directly in HTML, CSS, or JSON without a separate file.'],
      ['Can I decode Base64 back into an image?', 'Yes. Paste a Base64 string and the tool rebuilds the original image for preview and download.'],
      ['Is my data sent anywhere?', 'No. Encoding and decoding run locally in your browser, so nothing is uploaded.'],
    ],
  ],
  'favicon-generator' => [
    'guide' => [
      'Upload a square logo or image',
      'The tool generates favicons in all common sizes',
      'Preview how the icons will look',
      'Download every size packaged in a ZIP file',
    ],
    'faq' => [
      ['Which favicon sizes are created?', 'The tool produces the standard sizes browsers and devices need, from 16x16 up to larger app icons.'],
      ['Do I need a square image?', 'A square image works best, since favicons are square and non-square images may be cropped.'],
      ['Are my images uploaded?', 'No. All icons are generated in your browser before being zipped for download.'],
    ],
  ],
  'color-picker' => [
    'guide' => [
      'Upload the image you want to sample',
      'Click anywhere on the image to pick a colour',
      'Read the HEX and RGB values instantly',
      'Copy the colour code you need',
    ],
    'faq' => [
      ['What colour formats does it show?', 'It shows both HEX and RGB values for any pixel you click.'],
      ['Can I pick more than one colour?', 'Yes. Click different points to sample as many colours as you like.'],
      ['Is my image kept private?', 'Yes. Colour picking happens in your browser and your image is never uploaded.'],
    ],
  ],
  'exif-viewer' => [
    'guide' => [
      'Upload a photo to inspect its metadata',
      'View EXIF details such as camera, date, and location',
      'Choose to strip the metadata for privacy',
      'Download the cleaned image if you removed EXIF',
    ],
    'faq' => [
      ['What is EXIF data?', 'EXIF is hidden metadata in photos that can include camera settings, date, and even GPS location.'],
      ['Why remove EXIF before sharing?', 'Removing EXIF protects your privacy by stripping location and device details from the photo.'],
      ['Is the photo processed on your servers?', 'No. Reading and removing EXIF happens entirely in your browser.'],
    ],
  ],
  'meme-generator' => [
    'guide' => [
      'Upload an image or choose a base picture',
      'Type your top and bottom text',
      'Adjust font size and position',
      'Download your finished meme',
    ],
    'faq' => [
      ['Can I add both top and bottom captions?', 'Yes. Add text to the top, the bottom, or both, and style it to fit the image.'],
      ['Can I use my own picture?', 'Yes. Upload any image you like as the meme background.'],
      ['Is it free to create memes?', 'Yes. The free plan allows 3 memes per day, and premium removes the limit and any watermark.'],
    ],
  ],
  'dpi-changer' => [
    'guide' => [
      'Upload the image you want to prepare for print',
      'Set the new DPI, such as changing 72 to 300',
      'The tool updates the print resolution',
      'Download the image ready for high-quality printing',
    ],
    'faq' => [
      ['Does changing DPI change my file size?', 'DPI mainly affects print size, so the pixel data and file size usually stay about the same.'],
      ['Why set 300 DPI for printing?', 'Print shops often require 300 DPI so images look sharp rather than pixelated on paper.'],
      ['Is my image uploaded anywhere?', 'No. The DPI change is applied in your browser and your image stays private.'],
    ],
  ],
  'pdf-merge' => [
    'guide' => [
      'Upload two or more PDF files',
      'Drag them into the order you want',
      'The tool joins them into a single PDF',
      'Download the merged document',
    ],
    'faq' => [
      ['Can I control the order of merged files?', 'Yes. Drag the PDFs into any order before merging so pages appear exactly as you want.'],
      ['Are my PDFs uploaded to a server?', 'No. The merge runs in your browser, keeping your documents private.'],
      ['Is there a limit on how many PDFs I can merge?', 'The free plan covers everyday merges, while premium allows larger jobs without daily limits.'],
    ],
  ],
  'pdf-split' => [
    'guide' => [
      'Upload the PDF you want to split',
      'Choose to split every page or select ranges',
      'The tool separates the pages in your browser',
      'Download the resulting PDF files',
    ],
    'faq' => [
      ['Can I split into single pages?', 'Yes. You can break a PDF into one file per page or into custom page ranges.'],
      ['Is my PDF kept confidential?', 'Yes. Splitting is done locally in your browser and the file is never uploaded.'],
      ['Can I choose specific pages to split out?', 'Yes. Enter the page ranges you need instead of splitting the entire document.'],
    ],
  ],
  'pdf-compress' => [
    'guide' => [
      'Upload the PDF you want to shrink',
      'Choose a compression level',
      'The tool reduces the file size',
      'Download the smaller PDF',
    ],
    'faq' => [
      ['How much smaller will my PDF be?', 'Savings depend on the content, but PDFs with large images often shrink significantly.'],
      ['Will compressing lower the quality?', 'Text stays crisp, while images may soften slightly at higher compression levels.'],
      ['Is my PDF processed privately?', 'Yes. Compression happens in your browser, so your file is not uploaded.'],
    ],
  ],
  'pdf-to-word' => [
    'guide' => [
      'Upload the PDF you want to edit',
      'The tool extracts text and layout',
      'A Word document is generated for you',
      'Download the editable Word file',
    ],
    'faq' => [
      ['Will the Word file keep my formatting?', 'The tool preserves text and basic layout, though very complex designs may need minor touch-ups.'],
      ['Can scanned PDFs be converted?', 'Text-based PDFs convert best, while scanned images may need OCR to become editable text.'],
      ['Is my document safe?', 'Your file is handled securely and is not shared, so your content stays private.'],
    ],
  ],
  'word-to-pdf' => [
    'guide' => [
      'Upload your Word document',
      'The tool converts it while keeping the layout',
      'Preview the PDF result',
      'Download the finished PDF',
    ],
    'faq' => [
      ['Will fonts and layout stay the same?', 'Yes. The converter aims to keep your fonts, spacing, and layout intact in the PDF.'],
      ['Can I convert both .doc and .docx?', 'Yes. Common Word formats are supported for conversion to PDF.'],
      ['Is it free to convert?', 'Yes. The free plan allows 3 conversions per day, and premium removes the daily limit.'],
    ],
  ],
  'pdf-to-excel' => [
    'guide' => [
      'Upload a PDF that contains tables',
      'The tool detects and extracts the table data',
      'Review the extracted rows and columns',
      'Download the data as an Excel file',
    ],
    'faq' => [
      ['Does it keep my table columns?', 'Yes. The tool maps rows and columns into Excel cells so the structure is preserved.'],
      ['What if my PDF has no clear tables?', 'Well-structured tables convert best, while free-flowing text may not map neatly into cells.'],
      ['Is my file kept private?', 'Yes. Your PDF is processed securely and is not shared with anyone.'],
    ],
  ],
  'excel-to-pdf' => [
    'guide' => [
      'Upload your Excel spreadsheet',
      'The tool lays out the sheets for print',
      'Preview the PDF pages',
      'Download the converted PDF',
    ],
    'faq' => [
      ['Will my columns fit on the page?', 'The tool scales sheets to fit, and you can adjust orientation for wide tables.'],
      ['Can I convert multiple sheets?', 'Yes. All sheets in the workbook can be included in the PDF.'],
      ['Is my spreadsheet uploaded publicly?', 'No. Your file is handled privately and is never shared.'],
    ],
  ],
  'pdf-to-ppt' => [
    'guide' => [
      'Upload the PDF you want to present',
      'The tool turns each page into a slide',
      'Review the generated presentation',
      'Download the PowerPoint file',
    ],
    'faq' => [
      ['Does each PDF page become a slide?', 'Yes. Every page is placed on its own slide in the presentation.'],
      ['Can I edit the slides afterwards?', 'Yes. The output opens in PowerPoint so you can adjust text and layout.'],
      ['Is my document kept confidential?', 'Yes. Your PDF is processed securely and is not shared.'],
    ],
  ],
  'ppt-to-pdf' => [
    'guide' => [
      'Upload your PowerPoint presentation',
      'The tool converts each slide to a PDF page',
      'Preview the PDF version',
      'Download the finished PDF',
    ],
    'faq' => [
      ['Will animations be included?', 'PDF is a static format, so slides are exported as final images without animations.'],
      ['Can I share the PDF easily?', 'Yes. A PDF opens on any device, making it ideal for sharing your slides.'],
      ['Is it free to use?', 'Yes. The free plan allows 3 conversions per day, and premium unlocks unlimited use.'],
    ],
  ],
  'pdf-to-text' => [
    'guide' => [
      'Upload the PDF you want to read',
      'The tool extracts the text content',
      'Review the plain text output',
      'Copy or download the extracted text',
    ],
    'faq' => [
      ['Can I copy the extracted text?', 'Yes. The text appears on screen so you can copy it or download it as a file.'],
      ['Will it read scanned PDFs?', 'Digital PDFs extract cleanly, while scanned images may need OCR to recognise the text.'],
      ['Is my PDF uploaded to a server?', 'No. Text is extracted in your browser, keeping your document private.'],
    ],
  ],
  'pdf-rotate' => [
    'guide' => [
      'Upload the PDF with pages to turn',
      'Choose which pages to rotate and the direction',
      'The tool applies the rotation',
      'Download the corrected PDF',
    ],
    'faq' => [
      ['Can I rotate only some pages?', 'Yes. Select specific pages to rotate while leaving the rest untouched.'],
      ['Does rotating change the file quality?', 'No. Rotation only changes orientation and does not affect the content quality.'],
      ['Is my PDF processed privately?', 'Yes. Rotation happens in your browser, so your file is never uploaded.'],
    ],
  ],
  'pdf-delete-pages' => [
    'guide' => [
      'Upload the PDF you want to trim',
      'Select the pages you want to remove',
      'The tool rebuilds the PDF without them',
      'Download the smaller document',
    ],
    'faq' => [
      ['Can I delete several pages at once?', 'Yes. Choose any set of pages and remove them all in one step.'],
      ['Is the original PDF changed?', 'No. A new PDF is created, so your original file stays intact on your device.'],
      ['Is my file kept private?', 'Yes. Pages are removed in your browser and nothing is uploaded.'],
    ],
  ],
  'pdf-reorder' => [
    'guide' => [
      'Upload the PDF you want to rearrange',
      'Drag the page thumbnails into a new order',
      'The tool rebuilds the PDF in that sequence',
      'Download the reordered document',
    ],
    'faq' => [
      ['How do I change the page order?', 'Drag the page thumbnails into the order you want before saving.'],
      ['Does reordering affect quality?', 'No. Pages keep their original quality; only their sequence changes.'],
      ['Is my PDF uploaded to a server?', 'No. Reordering is done in your browser, keeping your document private.'],
    ],
  ],
  'pdf-extract-pages' => [
    'guide' => [
      'Upload the source PDF',
      'Enter the pages you want to keep',
      'The tool builds a new PDF from those pages',
      'Download the extracted PDF',
    ],
    'faq' => [
      ['Can I pull out a page range?', 'Yes. Enter single pages or ranges to create a new PDF with only those pages.'],
      ['Is the original file modified?', 'No. A separate PDF is created, leaving your original untouched.'],
      ['Is my document kept private?', 'Yes. Extraction runs in your browser, so nothing is uploaded.'],
    ],
  ],
  'pdf-protect' => [
    'guide' => [
      'Upload the PDF you want to secure',
      'Enter a password to lock the file',
      'The tool encrypts the document',
      'Download the password-protected PDF',
    ],
    'faq' => [
      ['What happens if I forget the password?', 'The password cannot be recovered, so store it safely before sharing the file.'],
      ['Does the password stop others opening it?', 'Yes. Anyone opening the PDF must enter the correct password first.'],
      ['Is my PDF processed securely?', 'Yes. The file is protected in your browser and is never uploaded to a server.'],
    ],
  ],
  'pdf-unlock' => [
    'guide' => [
      'Upload the password-protected PDF',
      'Enter the password you already know',
      'The tool removes the protection',
      'Download the unlocked PDF',
    ],
    'faq' => [
      ['Do I need to know the password?', 'Yes. This tool removes protection only from PDFs you can already open with the correct password.'],
      ['Can it crack an unknown password?', 'No. For security reasons the tool only unlocks files when you supply the valid password.'],
      ['Is my file kept private?', 'Yes. Unlocking happens in your browser, so your document is never uploaded.'],
    ],
  ],
  'pdf-page-numbers' => [
    'guide' => [
      'Upload the PDF you want to number',
      'Choose the position and starting number',
      'The tool stamps page numbers onto each page',
      'Download the numbered PDF',
    ],
    'faq' => [
      ['Where can the page numbers appear?', 'You can place numbers at the top or bottom and align them left, centre, or right.'],
      ['Can I start from a number other than one?', 'Yes. Set any starting number to match your document needs.'],
      ['Is my PDF uploaded anywhere?', 'No. Numbers are added in your browser, keeping the file private.'],
    ],
  ],
  'pdf-watermark' => [
    'guide' => [
      'Upload the PDF you want to mark',
      'Type your watermark text and set its style',
      'Adjust position and transparency',
      'Download the watermarked PDF',
    ],
    'faq' => [
      ['Can I control the watermark opacity?', 'Yes. Set the transparency so the watermark is subtle or clearly visible.'],
      ['Will the watermark appear on every page?', 'Yes. The text is applied across all pages by default.'],
      ['Is my document processed privately?', 'Yes. The watermark is added in your browser and the file is not uploaded.'],
    ],
  ],
  'pdf-metadata' => [
    'guide' => [
      'Upload the PDF you want to edit',
      'Update fields such as title and author',
      'The tool writes the new metadata',
      'Download the updated PDF',
    ],
    'faq' => [
      ['What metadata can I change?', 'You can edit details like the title, author, and subject stored inside the PDF.'],
      ['Does editing metadata change the pages?', 'No. Only the hidden document information changes; the page content stays the same.'],
      ['Is my file kept private?', 'Yes. Metadata is edited in your browser, so nothing is uploaded.'],
    ],
  ],
  'pdf-esign' => [
    'guide' => [
      'Upload the PDF you need to sign',
      'Draw your signature or upload an image of it',
      'Place and resize the signature on the page',
      'Download the signed PDF',
    ],
    'faq' => [
      ['Can I draw my signature directly?', 'Yes. Draw with your mouse or finger, or upload an existing signature image.'],
      ['Can I place the signature anywhere?', 'Yes. Drag the signature to any position and resize it to fit the document.'],
      ['Is my document kept confidential?', 'Yes. Signing happens in your browser, so your PDF is never uploaded to a server.'],
    ],
  ],
];
