<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/assets/css/theme.css">
</head>
<body>
    <div class="container">
        <div class="min-h-screen flex items-center justify-center">
            <div class="text-center">
                <h1 class="text-4xl mb-4">404</h1>
                <p class="text-2xl mb-4">Page Not Found</p>
                <p class="mb-5"><?= isset($message) ? e($message) : 'The page you are looking for does not exist.' ?></p>
                <a href="/" class="btn btn-primary">
                    <i class="material-icons mr-1">home</i>
                    Return to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>