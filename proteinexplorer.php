<?php
$servername = "127.0.0.1";
$username = "s2761220";
$password = "!AEZZ)C1aezz0c";
$email = "s2761220@ed.ac.uk";
include 'functions.php';
if(!isset($_COOKIE['user_id'])) {
    $unique_id = generateUUID();
    setcookie('user_id', $unique_id, time() + (86400 * 7), "/");
    $user_id = $unique_id;
} else {
    $user_id = $_COOKIE['user_id'];
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=s2761220_website", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_LOCAL_INFILE => true
    ]);

    maketables($conn);

    echo <<<_HEAD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProteinExplorer - Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <style>
        body { padding-top: 80px; background-color: #f8f9fa; }
        .search-card { border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .result-image { max-width: 80%; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
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
                    <li class="nav-item"><a class="nav-link" href="Default.php?search=all">Default Results</a></li>
                    <li class="nav-item"><a class="nav-link" href="HelpAndContext.php">Help</a></li>
                    <li class="nav-item"><a class="nav-link" href="CreditAndContacts.php">Credits</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
_HEAD;

if(isset($_POST['organism']) && isset($_POST['protein']) && !empty($_POST['protein']) && !empty($_POST['organism'])) {
        if (str_contains($_POST['organism'], ' ')) {
   $_POST['organism']=str_replace(' ', '_', $_POST['organism']);
	}
	        if (str_contains($_POST['protein'], ' ')) {
    $_POST['protein']=str_replace(' ', '_', $_POST['protein']);
        }
	$command = escapeshellcmd("python3 Backend.py " . escapeshellarg($user_id) . " " . escapeshellarg($_POST['organism']) . " " . escapeshellarg($_POST['protein']));
        $output = shell_exec($command);

        if (strpos($output,"No proteins found.")!==false) {
            echo <<<_FORM
            <div class="sci-card mt-4">
                <div class="sci-card-header">
                    <h3><i class="bi bi-exclamation-triangle me-2"></i>No Results Found</h3>
                </div>
                <div class="sci-card-body">
                    <form action="proteinexplorer.php" method="post">
                        <div class="alert alert-warning">
                            No proteins found for {$_POST['organism']} - {$_POST['protein']}. Please try different parameters.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-lg" name="organism" value="Aves" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-lg" name="protein" value="Glucose-6-phosphatase" required>
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-search me-2"></i>Search Again
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
_FORM;
        } else {
        $file_pathpep = $_POST['organism'] . "_" . $_POST['protein'] . "_" . "{$user_id}pepresults.txt";
	      $file_pathali = $_POST['organism'] . "_" . $_POST['protein'] . "_" . "{$user_id}alignment.fasta";
	      $file_pathpro = $_POST['organism'] . "_" . $_POST['protein'] . "_" . "{$user_id}resultsprosite.tsv";
	      $file_pathtsv = $_POST['organism'] . "_" . $_POST['protein'] . "_" . "{$user_id}results.tsv";
	      uploadtsv($file_pathtsv,$conn,"ts_table",["SeqName","Organism","Definition","Length"],$user_id);
	      uploadtsv($file_pathpro,$conn,"pro_table",["SeqName",	"Start",	"End","Score",	"Strand",	"Motif"],$user_id);
	      uploadtsv($file_pathpep,$conn,"pep_table",["SeqName",	"MolecularWeight",	"ResidueCount",	"ResidueWeight",	"Charge",	"IsoelectricPoint",	"ExtinctionReduced",	"ExtinctionBridges",	"ReducedMgMl",	"BridgeMgMl",	"Probability_pos_neg"],$user_id);
	      uploadfasta($file_pathali,$conn,$user_id);
            echo <<<_SUCCESS
            <div class="sci-card mt-4">
                <div class="sci-card-header bg-success text-white">
                    <h3><i class="bi bi-check-circle me-2"></i>Analysis Complete</h3>
                </div>
                <div class="sci-card-body">
                    <h4 class="mb-4">Results for {$_POST['protein']} in {$_POST['organism']}</h4>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="Results.php?search=all" class="btn btn-outline-primary">
                                    <i class="bi bi-folder2-open me-2"></i>Browse Results
                                </a>
                                <a href="{$user_id}results.zip" class="btn btn-outline-success">
                                    <i class="bi bi-download me-2"></i>Download Results
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="HelpAndContext.php" class="btn btn-outline-info">
                                    <i class="bi bi-question-circle me-2"></i>Help Documentation
                                </a>
                                <a href="CreditAndContacts.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-people me-2"></i>Credits & Contacts
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-5">
                        <img src="{$_POST['organism']}_{$_POST['protein']}_{$user_id}.plotcon.png" 
                             class="result-image img-fluid" 
                             alt="Analysis visualization">
                    </div>
                </div>
            </div>
_SUCCESS;
        }
    } else {
        echo <<<_FORM
        <div class="sci-card search-card mt-4">
            <div class="sci-card-header">
                <h2><i class="bi bi-search-heart me-2"></i>Protein Sequence Analysis</h2>
            </div>
            <div class="sci-card-body">
                <form action="proteinexplorer.php" method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Organism</label>
                            <input type="text" class="form-control form-control-lg" 
                                   name="organism" value="Aves" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Protein</label>
                            <input type="text" class="form-control form-control-lg" 
                                   name="protein" value="Glucose-6-phosphatase" required>
                        </div>
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-rocket-takeoff me-2"></i>Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
_FORM;
    }
    $stmt = $conn->prepare("SELECT * FROM pep_table WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($result) {
        echo "<div class='d-grid gap-2'>
                                <a href='Results.php?search=all' class='btn btn-outline-primary'>
                                    <i class='bi bi-folder2-open me-2'></i>Browse Results
                                </a></div>";
    }
    echo <<<_TAIL
    </main>
</body>
</html>
_TAIL;

} catch(PDOException $e) {
    echo "<div class='alert alert-danger mt-4'>Connection failed: " . $e->getMessage() . "</div>";
}
?>
