<?php
/*
captchaX Test File
v0.2, 2025-08-07
AGPLv3, @aaviator42
*/

header('Content-Type: text/plain');

// Set test configuration
define('CAPTCHAX_STORAGE_DIR', __DIR__ . '/test_captchas/');
define('CAPTCHAX_FONT_FILE', __DIR__ . '/CrayonLibre.ttf');

require 'captchaX.php';

// Initialize test environment
$testsPassed = true;

// Pre-test cleanup
if (is_dir(CAPTCHAX_STORAGE_DIR)) {
    captchaX\deleteDir(CAPTCHAX_STORAGE_DIR);
}

echo "Beginning captchaX Tests..." . PHP_EOL;
echo "Test file v0.2, 2025-08-07" . PHP_EOL;
echo "Storage Directory: " . CAPTCHAX_STORAGE_DIR . PHP_EOL;
echo "Font File: " . CAPTCHAX_FONT_FILE . PHP_EOL;
echo "GD Extension: " . (extension_loaded('gd') ? 'Available' : 'NOT AVAILABLE') . PHP_EOL;
echo "Font File Exists: " . (file_exists(CAPTCHAX_FONT_FILE) ? 'Yes' : 'No (will use built-in font)') . PHP_EOL;
echo PHP_EOL;

// Check GD extension availability
if (!extension_loaded('gd')) {
    echo "ERROR: GD extension is required for captchaX tests!" . PHP_EOL;
    echo "FINAL RESULTS: ALL TESTS *DID NOT* PASS!!!" . PHP_EOL;
    exit;
}

// Test 1: Basic CAPTCHA generation
echo "Test 1:     Basic CAPTCHA generation" . PHP_EOL;
echo "Function:   generate()" . PHP_EOL;
echo "Expecting:  Array with 'captchaText' and 'fileName' keys" . PHP_EOL;

try {
    $captcha = captchaX\generate();
    $result = is_array($captcha) && 
              isset($captcha['captchaText']) && 
              isset($captcha['fileName']) &&
              !empty($captcha['captchaText']) &&
              !empty($captcha['fileName']);
    
    echo "Result:     ";
    if ($result) {
        echo "CAPTCHA generated successfully (OK)" . PHP_EOL;
        echo "  Text: " . $captcha['captchaText'] . PHP_EOL;
        echo "  File: " . $captcha['fileName'];
    } else {
        echo "CAPTCHA generation failed (ERROR)" . PHP_EOL;
        if (is_array($captcha)) {
            echo "  Returned: " . var_export($captcha, true);
        } else {
            echo "  Returned: " . gettype($captcha);
        }
        $testsPassed = false;
    }
} catch (Exception $e) {
    echo "Result:     Exception: " . $e->getMessage() . " (ERROR)";
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 2: CAPTCHA text format validation
if (isset($captcha) && is_array($captcha)) {
    echo "Test 2:     CAPTCHA text format validation" . PHP_EOL;
    echo "Function:   Validate generated text format" . PHP_EOL;
    echo "Expecting:  4 characters from valid character set" . PHP_EOL;
    
    $validChars = '234678ABCDEFGHJKLMNPRSTUVWXY';
    $text = $captcha['captchaText'];
    $isValidLength = strlen($text) === 4;
    $hasValidChars = true;
    
    for ($i = 0; $i < strlen($text); $i++) {
        if (strpos($validChars, $text[$i]) === false) {
            $hasValidChars = false;
            break;
        }
    }
    
    echo "Result:     ";
    if ($isValidLength && $hasValidChars) {
        echo "Text format is valid: '$text' (OK)";
    } else {
        echo "Text format is invalid: '$text' (ERROR)" . PHP_EOL;
        echo "  Valid length (4): " . ($isValidLength ? 'Yes' : 'No') . PHP_EOL;
        echo "  Valid characters: " . ($hasValidChars ? 'Yes' : 'No');
        $testsPassed = false;
    }
} else {
    echo "Test 2:     SKIPPED (previous test failed)";
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 3: Image file creation
if (isset($captcha) && is_array($captcha)) {
    echo "Test 3:     Image file creation" . PHP_EOL;
    echo "Function:   Verify image file exists and is valid" . PHP_EOL;
    echo "Expecting:  PNG image file exists" . PHP_EOL;
    
    $imagePath = CAPTCHAX_STORAGE_DIR . $captcha['fileName'];
    $fileExists = file_exists($imagePath);
    $isValidImage = false;
    
    if ($fileExists) {
        $imageInfo = getimagesize($imagePath);
        $isValidImage = ($imageInfo !== false && $imageInfo['mime'] === 'image/png');
    }
    
    echo "Result:     ";
    if ($fileExists && $isValidImage) {
        echo "Image file created successfully (OK)" . PHP_EOL;
        echo "  Path: $imagePath" . PHP_EOL;
        echo "  Size: " . $imageInfo[0] . "x" . $imageInfo[1] . " pixels" . PHP_EOL;
        echo "  Type: " . $imageInfo['mime'];
    } else {
        echo "Image file creation failed (ERROR)" . PHP_EOL;
        echo "  File exists: " . ($fileExists ? 'Yes' : 'No') . PHP_EOL;
        echo "  Valid image: " . ($isValidImage ? 'Yes' : 'No');
        $testsPassed = false;
    }
} else {
    echo "Test 3:     SKIPPED (previous test failed)";
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 4: Directory structure creation
echo "Test 4:     Directory structure creation" . PHP_EOL;
echo "Function:   Verify date-based directory structure" . PHP_EOL;
echo "Expecting:  Directory created with current date" . PHP_EOL;

$expectedDir = CAPTCHAX_STORAGE_DIR . date('Y-m-d');
$dirExists = is_dir($expectedDir);

echo "Result:     ";
if ($dirExists) {
    echo "Date directory created successfully (OK)" . PHP_EOL;
    echo "  Directory: $expectedDir";
} else {
    echo "Date directory creation failed (ERROR)" . PHP_EOL;
    echo "  Expected: $expectedDir";
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 5: Multiple CAPTCHA generation
echo "Test 5:     Multiple CAPTCHA generation" . PHP_EOL;
echo "Function:   Generate 5 CAPTCHAs and verify uniqueness" . PHP_EOL;
echo "Expecting:  5 different CAPTCHA texts and filenames" . PHP_EOL;

$captchas = [];
$allUnique = true;
$generationErrors = [];

for ($i = 0; $i < 5; $i++) {
    try {
        $captcha = captchaX\generate();
        $captchas[] = $captcha;
    } catch (Exception $e) {
        $generationErrors[] = "CAPTCHA $i: " . $e->getMessage();
        $allUnique = false;
    }
}

// Check uniqueness
if (empty($generationErrors)) {
    $texts = array_column($captchas, 'captchaText');
    $filenames = array_column($captchas, 'fileName');
    
    $uniqueTexts = array_unique($texts);
    $uniqueFilenames = array_unique($filenames);
    
    $allUnique = (count($uniqueTexts) === count($texts) && count($uniqueFilenames) === count($filenames));
}

echo "Result:     ";
if ($allUnique && empty($generationErrors)) {
    echo "Multiple CAPTCHAs generated successfully (OK)" . PHP_EOL;
    echo "  Generated texts: " . implode(', ', array_column($captchas, 'captchaText'));
} else {
    echo "Multiple CAPTCHA generation failed (ERROR)" . PHP_EOL;
    if (!empty($generationErrors)) {
        foreach ($generationErrors as $error) {
            echo "  $error" . PHP_EOL;
        }
    }
    if (!$allUnique) {
        echo "  Not all CAPTCHAs were unique";
    }
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 6: Image dimensions verification
if (!empty($captchas)) {
    echo "Test 6:     Image dimensions verification" . PHP_EOL;
    echo "Function:   Verify all images are 200x50 pixels" . PHP_EOL;
    echo "Expecting:  All images have correct dimensions" . PHP_EOL;
    
    $correctDimensions = true;
    $dimensionInfo = [];
    
    foreach ($captchas as $captcha) {
        $imagePath = CAPTCHAX_STORAGE_DIR . $captcha['fileName'];
        if (file_exists($imagePath)) {
            $imageInfo = getimagesize($imagePath);
            $dimensionInfo[] = $imageInfo[0] . 'x' . $imageInfo[1];
            if ($imageInfo[0] !== 200 || $imageInfo[1] !== 50) {
                $correctDimensions = false;
            }
        } else {
            $correctDimensions = false;
            $dimensionInfo[] = 'FILE_MISSING';
        }
    }
    
    echo "Result:     ";
    if ($correctDimensions) {
        echo "All images have correct dimensions (OK)" . PHP_EOL;
        echo "  Dimensions: " . implode(', ', array_unique($dimensionInfo));
    } else {
        echo "Image dimensions are incorrect (ERROR)" . PHP_EOL;
        echo "  Found dimensions: " . implode(', ', $dimensionInfo);
        $testsPassed = false;
    }
} else {
    echo "Test 6:     SKIPPED (previous test failed)";
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 7: File cleanup simulation (create old date directory)
echo "Test 7:     File cleanup simulation" . PHP_EOL;
echo "Function:   Test automatic cleanup of old files" . PHP_EOL;
echo "Expecting:  Old directories are removed" . PHP_EOL;

// Create old date directory with test files
$oldDate = date('Y-m-d', strtotime('-1 day'));
$oldDir = CAPTCHAX_STORAGE_DIR . $oldDate;

if (!is_dir($oldDir)) {
    mkdir($oldDir, 0777, true);
}

// Create a dummy image file in old directory
$oldImagePath = $oldDir . '/old_test_image.png';
$image = imagecreatetruecolor(200, 50);
imagepng($image, $oldImagePath);
imagedestroy($image);

// Generate new CAPTCHA (should trigger cleanup)
$newCaptcha = captchaX\generate();

// Check if old directory was cleaned up
$oldDirExists = is_dir($oldDir);
$oldFileExists = file_exists($oldImagePath);

echo "Result:     ";
if (!$oldDirExists && !$oldFileExists) {
    echo "Old files cleaned up successfully (OK)";
} else {
    echo "Old file cleanup failed (ERROR)" . PHP_EOL;
    echo "  Old directory exists: " . ($oldDirExists ? 'Yes' : 'No') . PHP_EOL;
    echo "  Old file exists: " . ($oldFileExists ? 'Yes' : 'No');
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 8: Font fallback testing (simulate missing font)
echo "Test 8:     Font fallback testing" . PHP_EOL;
echo "Function:   Test behavior when TTF font is unavailable" . PHP_EOL;
echo "Expecting:  CAPTCHA still generated with built-in font" . PHP_EOL;

// Temporarily move/rename font file if it exists
$fontExists = file_exists(CAPTCHAX_FONT_FILE);
$fontBackup = CAPTCHAX_FONT_FILE . '.backup';

if ($fontExists) {
    rename(CAPTCHAX_FONT_FILE, $fontBackup);
}

try {
    $fallbackCaptcha = captchaX\generate();
    $fallbackSuccess = is_array($fallbackCaptcha) && 
                      !empty($fallbackCaptcha['captchaText']) && 
                      !empty($fallbackCaptcha['fileName']);
    
    echo "Result:     ";
    if ($fallbackSuccess) {
        echo "Font fallback works correctly (OK)" . PHP_EOL;
        echo "  Generated text: " . $fallbackCaptcha['captchaText'];
    } else {
        echo "Font fallback failed (ERROR)";
        $testsPassed = false;
    }
} catch (Exception $e) {
    echo "Result:     Font fallback exception: " . $e->getMessage() . " (ERROR)";
    $testsPassed = false;
}

// Restore font file
if ($fontExists && file_exists($fontBackup)) {
    rename($fontBackup, CAPTCHAX_FONT_FILE);
}
echo PHP_EOL . PHP_EOL;

// Test 9: Filename uniqueness over time
echo "Test 9:     Filename uniqueness over time" . PHP_EOL;
echo "Function:   Generate CAPTCHAs rapidly and check filename collisions" . PHP_EOL;
echo "Expecting:  No filename collisions in 20 rapid generations" . PHP_EOL;

$rapidCaptchas = [];
$filenameCollisions = 0;

for ($i = 0; $i < 20; $i++) {
    $captcha = captchaX\generate();
    if (in_array($captcha['fileName'], array_column($rapidCaptchas, 'fileName'))) {
        $filenameCollisions++;
    }
    $rapidCaptchas[] = $captcha;
    
    // Small delay to avoid potential timing issues
    usleep(10000); // 10ms
}

echo "Result:     ";
if ($filenameCollisions === 0) {
    echo "No filename collisions in 20 generations (OK)";
} else {
    echo "$filenameCollisions filename collision(s) detected (ERROR)";
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 10: Memory usage test
echo "Test 10:    Memory usage test" . PHP_EOL;
echo "Function:   Generate 50 CAPTCHAs and monitor memory usage" . PHP_EOL;
echo "Expecting:  Memory usage remains reasonable" . PHP_EOL;

$startMemory = memory_get_usage();
$peakMemory = $startMemory;

for ($i = 0; $i < 50; $i++) {
    captchaX\generate();
    $currentMemory = memory_get_usage();
    if ($currentMemory > $peakMemory) {
        $peakMemory = $currentMemory;
    }
}

$endMemory = memory_get_usage();
$memoryIncrease = $endMemory - $startMemory;
$peakIncrease = $peakMemory - $startMemory;

echo "Result:     ";
if ($memoryIncrease < 5 * 1024 * 1024) { // Less than 5MB increase
    echo "Memory usage is reasonable (OK)" . PHP_EOL;
    echo "  Memory increase: " . round($memoryIncrease / 1024, 2) . " KB" . PHP_EOL;
    echo "  Peak increase: " . round($peakIncrease / 1024, 2) . " KB";
} else {
    echo "Memory usage is too high (ERROR)" . PHP_EOL;
    echo "  Memory increase: " . round($memoryIncrease / 1024, 2) . " KB" . PHP_EOL;
    echo "  Peak increase: " . round($peakIncrease / 1024, 2) . " KB";
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 11: File extension validation
echo "Test 11:    File extension validation" . PHP_EOL;
echo "Function:   Verify all generated files have .png extension" . PHP_EOL;
echo "Expecting:  All files end with .png" . PHP_EOL;

$extensionTest = true;
$testCaptcha = captchaX\generate();

if (!str_ends_with($testCaptcha['fileName'], '.png')) {
    $extensionTest = false;
}

// Check a few files from previous tests
foreach (array_slice($rapidCaptchas, 0, 3) as $captcha) {
    if (!str_ends_with($captcha['fileName'], '.png')) {
        $extensionTest = false;
        break;
    }
}

echo "Result:     ";
if ($extensionTest) {
    echo "All files have correct .png extension (OK)";
} else {
    echo "Some files have incorrect extensions (ERROR)";
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 12: Storage directory permissions
echo "Test 12:    Storage directory permissions" . PHP_EOL;
echo "Function:   Test write permissions in storage directory" . PHP_EOL;
echo "Expecting:  Directory is writable" . PHP_EOL;

$testFile = CAPTCHAX_STORAGE_DIR . 'permission_test.txt';
$canWrite = false;

try {
    file_put_contents($testFile, 'test');
    $canWrite = file_exists($testFile);
    if ($canWrite) {
        unlink($testFile);
    }
} catch (Exception $e) {
    // Write failed
}

echo "Result:     ";
if ($canWrite) {
    echo "Storage directory is writable (OK)";
} else {
    echo "Storage directory is not writable (ERROR)";
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Test 13: Character set validation
echo "Test 13:    Character set validation" . PHP_EOL;
echo "Function:   Generate 100 CAPTCHAs and verify character distribution" . PHP_EOL;
echo "Expecting:  Good distribution across valid character set" . PHP_EOL;

$validChars = '234678ABCDEFGHJKLMNPRSTUVWXY';
$charCount = array_fill_keys(str_split($validChars), 0);
$totalChars = 0;

for ($i = 0; $i < 100; $i++) {
    $captcha = captchaX\generate();
    $text = $captcha['captchaText'];
    
    for ($j = 0; $j < strlen($text); $j++) {
        $char = $text[$j];
        if (isset($charCount[$char])) {
            $charCount[$char]++;
        }
        $totalChars++;
    }
}

// Check if distribution is reasonable (no character completely unused in 400 total chars)
$unusedChars = array_keys(array_filter($charCount, function($count) { return $count === 0; }));
$distributionGood = count($unusedChars) < (strlen($validChars) * 0.3); // Less than 30% unused

echo "Result:     ";
if ($distributionGood) {
    echo "Character distribution is good (OK)" . PHP_EOL;
    echo "  Unused characters: " . count($unusedChars) . " out of " . strlen($validChars);
} else {
    echo "Character distribution is poor (ERROR)" . PHP_EOL;
    echo "  Unused characters: " . implode(', ', $unusedChars);
    $testsPassed = false;
}
echo PHP_EOL . PHP_EOL;

// Final cleanup
if (is_dir(CAPTCHAX_STORAGE_DIR)) {
    // captchaX\deleteDir(CAPTCHAX_STORAGE_DIR);
}

echo "Tests completed!" . PHP_EOL;
if ($testsPassed === true) {
    echo "FINAL RESULTS: ALL TESTS PASSED!!!";
} else {
    echo "FINAL RESULTS: ALL TESTS *DID NOT* PASS!!!" . PHP_EOL;
    echo "See errors above." . PHP_EOL;
}

?>