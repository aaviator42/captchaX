<?php

/*

captchaX by @aaviator42
text-in-image captcha generation

v0.2, 2025-08-07
AGPLv3

*/
namespace captchaX;

// Configuration constant for storage directory
// Override this constant before including the library to change storage location, or edit it below
if (!defined('CAPTCHAX_STORAGE_DIR')) {
    define('CAPTCHAX_STORAGE_DIR', __DIR__ . '/pub/captchas/');
}

// Configuration constant for font file
// Override this constant before including the library to change font file, or edit it below
if (!defined('CAPTCHAX_FONT_FILE')) {
    define('CAPTCHAX_FONT_FILE', __DIR__ . '/CrayonLibre.ttf');
}

// Function to recursively delete a directory and its contents
function deleteDir($dirPath) {
    if (!is_dir($dirPath)) {
        return;
    }
    
    $files = array_diff(scandir($dirPath), array('.', '..'));
    
    foreach ($files as $file) {
        $filePath = $dirPath . '/' . $file;
        is_dir($filePath) ? deleteDir($filePath) : unlink($filePath);
    }
    
    rmdir($dirPath);
}

// Function to generate the CAPTCHA image
function generate() {
    // Set the directory where captchas will be saved
    $captchasDir = CAPTCHAX_STORAGE_DIR;
    
    // Get all subdirectories in the captchas directory
    $subDirs = glob($captchasDir . '*', GLOB_ONLYDIR);
	
	
    // Delete existing subdirectories
    foreach ($subDirs as $dir) {
        if(realpath($dir) === realpath($captchasDir . date('Y-m-d'))) {
			continue; //skip current date captcha subfolder
		} else {
			deleteDir($dir); //delete all other captcha subfolders
		}
    }

    // Create the image
    $image = \imagecreatetruecolor(200, 50);

    // Set colors
    $bgColor = \imagecolorallocate($image, 255, 255, 255); // white background
    $textColor = \imagecolorallocate($image, 255, 0, 0);   // red text
    $lineColor = \imagecolorallocate($image, 64, 64, 64);  // grey lines

    // Fill the background with white
    \imagefilledrectangle($image, 0, 0, 200, 50, $bgColor);

    // Generate a random string for the CAPTCHA
    $characters = '234678ABCDEFGHJKLMNPRSTUVWXY';
    $captchaText = '';
    $captchaLength = 4;
    for ($i = 0; $i < $captchaLength; $i++) {
        $captchaText .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    // Store the CAPTCHA text in the session
    // $_SESSION['captcha'] = $captchaText;

    // Add random lines to make it harder for bots
    for ($i = 0; $i < 10; $i++) {
        imageline($image, mt_rand(0, 200), mt_rand(0, 50), mt_rand(0, 200), mt_rand(0, 50), $lineColor);
    }

    // Add the text to the image using a random font size
    $fontSize = 25;
    $fontFile = CAPTCHAX_FONT_FILE;

    if (file_exists($fontFile)) {
        \imagettftext($image, $fontSize, mt_rand(-10, 10), mt_rand(10, 60), mt_rand(30, 40), $textColor, $fontFile, $captchaText);
    } else {
        // If the font is not available, use default font
        \imagestring($image, 5, 50, 15, $captchaText, $textColor);
    }

    // Create the directory path based on the current date
    $datePath = date('Y-m-d');
    $directory = $captchasDir . "{$datePath}/";

    // Create the directory if it doesn't exist
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Generate a random 10-character filename
    $randomFilename = substr(str_shuffle($characters), 0, 10) . '.png';
    $filePath = $directory . $randomFilename;

    // Save the image with the random filename
    \imagepng($image, $filePath);

    // Free memory
    \imagedestroy($image);

    // Return the CAPTCHA text and the file path
    return [
        'captchaText' => $captchaText,
        'fileName' => $datePath . '/' . $randomFilename,
        // 'filePath' => $filePath
    ];
	// return $filePath;
}


?>
