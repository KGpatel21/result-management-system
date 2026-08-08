<?php
// ============================================================
// FILE: captcha.php
// PURPOSE: Generate a CAPTCHA image with random letters
// HOW IT WORKS:
//   1. Generate 5 random letters
//   2. Save them in a SESSION variable (so we can check later)
//   3. Draw them on a PNG image
//   4. Send the image to the browser
// ============================================================

// Start session to store the CAPTCHA text
session_start();

// Step 1: Generate 5 random characters
$captcha_text = '';
$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
// Note: We removed confusing characters like 0/O, 1/l/I

for ($i = 0; $i < 5; $i++) {
    // Pick a random character from the string
    $captcha_text .= $characters[rand(0, strlen($characters) - 1)];
}

// Step 2: Save the CAPTCHA text in session
// When student submits the form, we compare their input with this value
$_SESSION['captcha'] = $captcha_text;

// Step 3: Create the image
$width = 150;   // Image width in pixels
$height = 40;   // Image height in pixels
$image = imagecreate($width, $height);  // Create a blank image

// Set colors
$background = imagecolorallocate($image, 255, 255, 255);  // White background
$text_color = imagecolorallocate($image, 50, 50, 100);     // Dark blue text
$line_color = imagecolorallocate($image, 200, 200, 200);   // Grey lines (noise)

// Step 4: Add some noise lines (makes it harder for bots to read)
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height),
              rand(0, $width), rand(0, $height), $line_color);
}

// Step 5: Draw the CAPTCHA text on the image
// We use imagestring() which is the simplest function (no special fonts needed)
imagestring($image, 5, 30, 10, $captcha_text, $text_color);
// Parameters: image, font-size(1-5), x-position, y-position, text, color

// Step 6: Output the image as PNG
header('Content-Type: image/png');  // Tell browser "this is an image, not HTML"
imagepng($image);                   // Send the PNG image
imagedestroy($image);               // Free memory
?>
