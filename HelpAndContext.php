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
    <title>Help Center - ProteinExplorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        body { 
            padding-top: 80px; 
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #003366 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: #fff !important;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="proteinexplorer.php">ProteinExplorer</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
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

    <main class="container">
        <div class="sci-card">
            <div class="sci-card-header">
                <h1 class="sci-card-title">Help Center</h1>
                <div class="user-id-badge">
                    Your Session ID: <span class="badge bg-primary">{$user_id}</span>
                </div>
            </div>
            <div class="sci-card-body">
                <div class="help-section">
                    <h3 class="section-title">About This Platform</h3>
                    <p class="lead">A bioinformatics analysis tool for protein sequence retrieval and analysis across taxonomic groups.</p>
                    
                    <div class="feature-list">
                        <div class="feature-card">
                            
                            <h4>Key Capabilities</h4>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle me-2"></i>Fetch & analyze protein sequences using EMBOSS tools</li>
                                <li><i class="bi bi-check-circle me-2"></i>Store results with 7-day retention</li>
                                <li><i class="bi bi-check-circle me-2"></i>Comparative analysis between species</li>
                                <li><i class="bi bi-check-circle me-2"></i>Generate custom BLAST databases</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="help-section mt-5">
                    <h3 class="section-title">User Guide</h3>
                    <div class="guide-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h5>Basic Search</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <i class="bi bi-search me-2"></i>
                                    Enter organism name and protein of interest
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-play-circle me-2"></i>
                                    Click Search to initiate analysis
                                </li>
                                <li class="list-group-item">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Invalid searches return with error messages
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="guide-step mt-4">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h5>Advanced Features</h5>
                            <div class="alert alert-info">
                                <i class="bi bi-shield-check me-2"></i>
                                <strong>Motif Analysis:</strong> Use "MOTIF, [sequence]" format in search
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-shield-check me-2"></i>
                                <strong>Alignment Analysis:</strong> Use "ALIGNMENT, [sequence]" format in search
                            </div>
                            <div class="alert alert-info">
                                <i class="bi bi-clock-history me-2"></i>
                                <strong>Result Management:</strong> Automatically cleared after 7 days
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
_HEAD;
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>