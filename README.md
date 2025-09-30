# captchaX
Advanced CAPTCHA generation library with TTF font support  
`v0.2`, `2025-08-07`  
by @aaviator42  
License: `AGPLv3`  

## Overview

captchaX is a PHP library for generating secure, customizable CAPTCHA images with TTF font support, automatic cleanup, and date-based organization. Designed to prevent automated bot attacks while maintaining a good user experience.

## Features

- **TTF font support** - Professional typography with configurable fonts
- **Configurable difficulty** - Adjustable character sets and complexity
- **Date-based cleanup** - Automatic removal of old CAPTCHA files
- **Random distortion** - Anti-OCR lines and rotation for security
- **Flexible storage** - Configurable storage location and font paths
- **High entropy** - Cryptographically secure random generation
- **Memory efficient** - Automatic image resource cleanup

## Installation

1. Include the library in your project:
```php
require_once 'lib/captchaX.php';
```

2. Ensure GD extension is available:
```php
if (!extension_loaded('gd')) {
    die('GD extension required for captchaX');
}
```

3. Place a TTF font file in your lib directory (default: `CrayonLibre.ttf` - Crayon Libre font by GGBot.net)

## Configuration

### Storage Directory
Override the storage directory before including the library:
```php
define('CAPTCHAX_STORAGE_DIR', '/custom/path/to/captcha/storage/');
require_once 'lib/captchaX.php';
```

### Font File
Override the font file before including the library:
```php
define('CAPTCHAX_FONT_FILE', '/path/to/custom/font.ttf');
require_once 'lib/captchaX.php';
```

### Multiple Configuration Example
```php
// Custom configuration
define('CAPTCHAX_STORAGE_DIR', __DIR__ . '/assets/captchas/');
define('CAPTCHAX_FONT_FILE', __DIR__ . '/fonts/RobotoMono-Bold.ttf');

require_once 'lib/captchaX.php';
```

## Directory Structure

```
{CAPTCHAX_STORAGE_DIR}/
├── 2025-08-07/     [current day]
│   ├── A7K9P2X4B8.png
│   ├── M3H6T9Q2L5.png
│   └── R8Y4N1C7F3.png
├── 2025-08-06/
│   └── [previous day's CAPTCHA files]
└── 2025-08-05/
    └── [older files, auto-deleted]
```

## API Reference

### Core Function

#### generate()
Generate a new CAPTCHA image and return metadata.

```php
use captchaX;

$captcha = captchaX\generate();

// Returns array with:
// [
//     'captchaText' => 'A7K9',           // The solution text
//     'fileName' => '2025-08-07/A7K9P2X4B8.png'  // Relative file path
// ]
```

## Usage Examples

### Basic Implementation
```php
session_start();
require_once 'lib/captchaX.php';

// Generate CAPTCHA
$captcha = captchaX\generate();
$_SESSION['captcha_solution'] = $captcha['captchaText'];
$imagePath = 'pub/captchas/' . $captcha['fileName'];

// Display in HTML
echo '<img src="' . $imagePath . '" alt="CAPTCHA">';
echo '<input type="text" name="captcha" placeholder="Enter CAPTCHA">';
```

### Verification
```php
session_start();

if (isset($_POST['captcha'])) {
    $userInput = strtoupper(trim($_POST['captcha']));
    $solution = $_SESSION['captcha_solution'] ?? '';
    
    if ($userInput === $solution) {
        echo "CAPTCHA verified successfully!";
        // Clear used CAPTCHA
        unset($_SESSION['captcha_solution']);
    } else {
        echo "CAPTCHA verification failed!";
    }
}
```

### Complete Form Example

Open `demo.php` in a browser. 

```php
<?php
session_start();
require_once 'captchaX.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = strtoupper(trim($_POST['captcha'] ?? ''));
    $solution = $_SESSION['captcha_solution'] ?? '';
    
    if ($userInput === $solution) {
        $success = true;
        unset($_SESSION['captcha_solution']);
    } else {
        $error = 'CAPTCHA verification failed. Please try again.';
    }
}

// Generate new CAPTCHA for form
$captcha = captchaX\generate();
$_SESSION['captcha_solution'] = $captcha['captchaText'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>CAPTCHA Example</title>
</head>
<body>
    <?php if ($success): ?>
        <p style="color: green;">Form submitted successfully!</p>
    <?php else: ?>
        <form method="post">
            <div>
                <label>Enter the text shown in the image:</label><br>
                <img src="pub/captchas/<?php echo $captcha['fileName']; ?>" alt="CAPTCHA"><br>
                <input type="text" name="captcha" required>
            </div>
            
            <?php if ($error): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            
            <button type="submit">Submit</button>
        </form>
    <?php endif; ?>
</body>
</html>
```

## CAPTCHA Characteristics

### Visual Properties
- **Image size:** 200x50 pixels
- **Background:** White (#FFFFFF)
- **Text color:** Red (#FF0000)
- **Noise lines:** Gray (#404040)
- **Font size:** 25pt

### Security Features
- **Character set:** `234678ABCDEFGHJKLMNPRSTUVWXY` (excludes confusing characters)
- **Length:** 4 characters
- **Rotation:** Random -10° to +10° per character
- **Position:** Random placement within bounds
- **Noise:** 10 random lines for OCR resistance
- **Fallback:** Uses built-in font if TTF unavailable

### Accessibility Considerations
```php
// Generate audio CAPTCHA alternative (pseudo-code)
function generateAudioCaptcha($text) {
    // Implementation would use text-to-speech
    // or pre-recorded character sounds
}

// In your form
echo '<audio controls>';
echo '<source src="audio_captcha.php?id=' . $captchaId . '" type="audio/wav">';
echo 'Your browser does not support audio CAPTCHA.';
echo '</audio>';
```

## Security Best Practices

### Session Security
```php
// Always use sessions for CAPTCHA storage
session_start();
if (!isset($_SESSION)) {
    die('Sessions must be enabled for CAPTCHA');
}

// Use CSRF tokens with CAPTCHA
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

### One-Time Use
```php
// Clear CAPTCHA after verification attempt
unset($_SESSION['captcha_solution']);

// Generate new CAPTCHA for each form display
$captcha = captchaX\generate();
$_SESSION['captcha_solution'] = $captcha['captchaText'];
```

### Rate Limiting Integration
```php
// Limit CAPTCHA generation requests
$lastGenerated = $_SESSION['last_captcha_time'] ?? 0;
if (time() - $lastGenerated < 2) {
    die('Please wait before requesting new CAPTCHA');
}
$_SESSION['last_captcha_time'] = time();
```

## Customization

### Custom Character Sets
Modify the character set in the library:
```php
// In captchaX.php, change line ~66:
$characters = 'ABCDEF123456';  // Hexadecimal only
$characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';  // Original
$characters = '0123456789';  // Numbers only
```

### Difficulty Adjustment
```php
// Modify these values in generate() function:
$captchaLength = 5;     // Longer text
$fontSize = 30;         // Larger font
$lineCount = 15;        // More noise lines
$rotationRange = 15;    // More rotation
```

### Visual Styling
```php
// Modify colors in generate() function:
$bgColor = imagecolorallocate($image, 240, 240, 240);    // Light gray background
$textColor = imagecolorallocate($image, 0, 0, 139);      // Dark blue text
$lineColor = imagecolorallocate($image, 128, 128, 128);  // Medium gray lines

// Modify image dimensions:
$image = imagecreatetruecolor(300, 80);  // Larger image
```

## File Management

### Data Retention Policy
captchaX implements automatic data cleanup for security and storage efficiency:
- **Retention period:** Current day only (24-hour maximum retention)
- **Cleanup frequency:** Automatic cleanup runs on each generate() call
- **Storage scope:** CAPTCHA images and metadata are removed daily
- **Purpose:** Prevents storage bloat and ensures old CAPTCHA challenges cannot be reused

### Automatic Cleanup
captchaX automatically removes old CAPTCHA files:
- Keeps current day's files only
- Runs cleanup on each generate() call
- Prevents storage bloat

### Manual Cleanup
```php
// Clean specific date
$dateDir = CAPTCHAX_STORAGE_DIR . '2025-08-01/';
if (is_dir($dateDir)) {
    // Use captchaX's deleteDir function
    captchaX\deleteDir($dateDir);
}

// Clean all old files
$allDirs = glob(CAPTCHAX_STORAGE_DIR . '*', GLOB_ONLYDIR);
$today = CAPTCHAX_STORAGE_DIR . date('Y-m-d');

foreach ($allDirs as $dir) {
    if ($dir !== $today) {
        captchaX\deleteDir($dir);
    }
}
```

### Storage Monitoring
```php
function getCaptchaStorageStats() {
    $totalSize = 0;
    $fileCount = 0;
    $directories = glob(CAPTCHAX_STORAGE_DIR . '*', GLOB_ONLYDIR);
    
    foreach ($directories as $dir) {
        $files = glob($dir . '/*.png');
        $fileCount += count($files);
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
    }
    
    return [
        'total_size' => $totalSize,
        'file_count' => $fileCount,
        'directories' => count($directories)
    ];
}
```

## Performance Considerations

### Resource Usage
- Each CAPTCHA generation uses ~50KB memory peak
- Generated images are typically 2-5KB each
- TTF rendering is more resource-intensive than built-in fonts

### Optimization Tips
```php
// Pre-check font availability
if (!file_exists(CAPTCHAX_FONT_FILE)) {
    // Use simpler built-in font rendering
    // or show appropriate error
}

// Limit concurrent generations
$lockFile = sys_get_temp_dir() . '/captcha_gen.lock';
if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 1) {
    sleep(1); // Brief delay if generation in progress
}
touch($lockFile);
```

### Caching Considerations
```php
// Don't cache CAPTCHA images
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Serve CAPTCHA images
function serveCaptchaImage($fileName) {
    $filePath = CAPTCHAX_STORAGE_DIR . $fileName;
    if (file_exists($filePath)) {
        header('Content-Type: image/png');
        readfile($filePath);
        exit;
    }
    http_response_code(404);
}
```

## Troubleshooting

### Debug Information
```php
// Check configuration
echo "Storage Dir: " . CAPTCHAX_STORAGE_DIR . "\n";
echo "Font File: " . CAPTCHAX_FONT_FILE . "\n";
echo "Font Exists: " . (file_exists(CAPTCHAX_FONT_FILE) ? 'Yes' : 'No') . "\n";
echo "GD Version: " . gd_info()['GD Version'] . "\n";
echo "TTF Support: " . (function_exists('imagettftext') ? 'Yes' : 'No') . "\n";

// Test generation
try {
    $captcha = captchaX\generate();
    echo "Generation successful: " . $captcha['fileName'] . "\n";
} catch (Exception $e) {
    echo "Generation failed: " . $e->getMessage() . "\n";
}
```

## License

AGPLv3 - See license file for details

## Dependencies

- PHP 8.0+
- GD extension with TTF support
- Write permissions for storage directory
- TTF font file (included default or custom)

## Author

@aaviator42

-----
Documentation updated: `2025-09-30`