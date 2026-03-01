<?php
$servername = "127.0.0.1";
$username = "s2761220";
$password = "!AEZZ)C1aezz0c";
$email = "s2761220@ed.ac.uk";
include 'functions.php';

if(!isset($_COOKIE['user_id'])) {
    $unique_id = generateUUID();
    setcookie("user_id", $unique_id, time() + (86400 * 7), "/");
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = $_COOKIE['user_id'];
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=s2761220_website", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_LOCAL_INFILE => true
    ]);

    echo <<<_HEAD
     <!DOCTYPE html>
     <html lang="en">
     <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits - ProteinExplorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
    body { padding-top: 70px; }
        </style>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="proteinexplorer.php">ProteinExplorer</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="Default.php?search=all">Default Results</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="HelpAndContext.php">Help</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="CreditAndContacts.php">Credits</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    _HEAD;

    echo <<<_CONTENT
    <div class="main-container">
        <div class="sci-card">
            <div class="sci-card-header">
                <h1 class="sci-card-title">Credits and Contacts</h1>
            </div>
            <div class="sci-card-body">
                <div class="credits-content">
                    <p>Thank you for using the Protein Explorer. The code used to create this site is available 
                       here: <a href='https://github.com/B270934-2024/WebsiteAssignment' class="btn-bio">On Github</a>.</p>
                    
                    <div class="acknowledgments">
                        <h3>Acknowledgments</h3>
                        <ul>
                            <li>StackOverflow community for coding assistance</li>
                            <li>EMBOSS tools developers</li>
                            <li>NCBI for their excellent database</li>
                            <li>DeepSeek for help with styling choices and bugfixing</li>
                            <li>Al Iverns and coursemates for support, inspiration, and further help</li>
                        </ul>
                    </div>
                    
                    <div class="contact-info">
                        <h3>Contact</h3>
                        <p>For questions or suggestions: 
                           <a href="mailto:s2761220@ed.ac.uk" class="btn-bio btn-bio-secondary">Email Me</a></p>
                    </div>
                </div>
                
                <div class="action-buttons mt-5">
    _CONTENT;

    echo"<div class='action-buttons mt-5'><a href='Default.php?search=all' class='btn btn-primary me-3'>
                        <i class='bi bi-database me-2'></i>Example Results
                    </a>";
    
   
    echo <<<_FOOTER
                
                <a href='proteinexplorer.php' class='btn btn-outline-primary'>
                        <i class="bi bi-search me-2"></i>New Search
                    </a>
            </div>
            </div>
        </div>
    </div>
    </body>
    </html>
    _FOOTER;

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
