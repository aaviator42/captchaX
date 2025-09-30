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
