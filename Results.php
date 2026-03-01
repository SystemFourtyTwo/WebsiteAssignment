<?php
$servername = "127.0.0.1"; //localhost didn't work
$username = "s2761220";
$password = "!AEZZ)C1aezz0c";
$email="s2761220@ed.ac.uk";
include'functions.php';
$user_id=$_COOKIE['user_id'];
echo <<<_HEAD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protein Database - ProteinExplorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://d3js.org/d3.v7.min.js"></script>
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
    <link rel="stylesheet" href="style/style.css">
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
_HEAD;

$conn = new PDO("mysql:host=$servername;dbname=s2761220_website", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    PDO::MYSQL_ATTR_LOCAL_INFILE => true
  ]);
echo	"<content><h1>Your Database</h1>";
echo "<form method='GET' action=''>
        <label for='search'>Search by Sequence Name: Enter comma separated list of SeqNames or 'all'.
Alternatively, type 'MOTIF, or ALIGNMENT,' and your chosen sequence, to investigate that further.</label>
        <p>Your User ID is: {$user_id}</p>
        <div class='d-flex justify-content-between gap-3 mt-3'>
        <input type='text' class = 'form-control me-2' id='search' name='search' placeholder='Enter SeqName or all...' required>
        <button type='submit' class='btn btn-primary'>Search</button>
        </div></form>";
	echo "<div class='d-grid gap-2'>
                                <a href='proteinexplorer.php' class='btn btn-outline-primary'>
                        <i class='bi bi-search me-2'></i>New Search
                    </a>
                                <a href='{$user_id}results.zip' class='btn btn-outline-success'>
                                    <i class='bi bi-download me-2'></i>Download Results
                                </a>
                            </div>";

echo <<<CLEAR_FORM
<style>
    .btn-clear-results {
        background: white;
        color: #dc3545;
        border: 2px solid #dc3545;
        transition: all 0.3s ease;
    }
    .btn-clear-results:hover {
        background: #dc3545;
        color: white;
        border-color: #dc3545;
    }
    .btn-clear-results:focus {
        box-shadow: 0 0 0 0.25rem rgba(220,53,69,.3);
    }
</style>

<div class="mt-4 text-center">
    <form method='POST' action='' onsubmit="return confirm('Warning: This will permanently delete all your results. Continue?')">
        <input type='hidden' name='clear_results' value='1'>
        <button type='submit' class='btn btn-clear-results btn-lg px-4 py-2'>
            <i class='bi bi-trash3 me-2'></i>Clear All Results
        </button>
        <p class='text-danger small mt-2 fw-semibold'>
            <i class='bi bi-exclamation-triangle-fill'></i> This action is permanent
        </p>
    </form>
</div>
CLEAR_FORM;

$input = isset($_GET['search']) ? $_GET['search'] : 'all';


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["clear_results"])) {
    clearUserResults($conn, $user_id);
	echo "<p>Your results have been cleared. Please return to the previous page.</p>";
}

if (isset($_GET['search'])) {
    $input = $_GET['search'];
    if ($input === "DELETE!AEZZ)C1aezz0c") {
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $conn->exec("DROP TABLE IF EXISTS `$table`");
        }
        exit; // Stop execution after deleting tables
    } elseif ($input === "") {
        exit; // Stop execution if search is empty
    }
}
$isSingleSeq = false;
$searchParts = explode(',', $input);
if (count($searchParts) === 1 && $input !== 'all') {
    // Simple single sequence search
    $isSingleSeq = true;
} elseif (count($searchParts) === 2 && in_array(strtoupper(trim($searchParts[0])), ['MOTIF', 'ALIGNMENT'])) {
    // MOTIF/ALIGNMENT search for single sequence
    $isSingleSeq = true;
}
if ($isSingleSeq) {
    // Extract actual sequence name from input
    $seqName = $input;
    if (count($searchParts) === 2) {
        $seqName = trim($searchParts[1]);
    }
}
   // In your Results.php after checking $isSingleSeq
try {
    // Get ACTUAL sequence name from input
    $seqName = $input;
    if (count($searchParts) === 2) {
        $seqName = trim($searchParts[1]); // For MOTIF,SEQNAME format
    }

    // Get motifs for THIS SPECIFIC sequence
    $stmt = $conn->prepare("SELECT Start, End, Motif, Score, Strand 
                          FROM pro_table 
                          WHERE user_id = ? AND SeqName = ?");
    $stmt->execute([$user_id, $seqName]);
    $motifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get PROTEIN LENGTH from ts_table
    $stmt = $conn->prepare("SELECT Length FROM ts_table 
                          WHERE user_id = ? AND SeqName = ?");
    $stmt->execute([$user_id, $seqName]);
    $proteinLength = $stmt->fetchColumn() ?: 100; // Default to 100 if missing

    echo "<script>
            const domainData = {
                length: $proteinLength,
                motifs: " . json_encode($motifs) . "
            };
          </script>";

} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error loading data: " 
        . $e->getMessage() . "</div>";
}
// Domain Visualization Section
echo <<<DOMAIN_VIS
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-columns"></i> Domain Architecture Visualization
    </div>
    <div id="domain-visualization"></div>
</div>
DOMAIN_VIS;

// Prepare motif data for D3.js
try {
    $stmt = $conn->prepare("SELECT Start, End, Motif, Score, Strand FROM pro_table WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $motifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $proteinLength = 0;
    foreach ($motifs as $motif) {
        if ($motif['End'] > $proteinLength) {
            $proteinLength = $motif['End'];
        }
    }
    
    echo "<script>
            const domainData = {
                length: $proteinLength,
                motifs: " . json_encode($motifs) . "
            };
          </script>";
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error loading motif data: " . $e->getMessage() . "</div>";
}
echo <<<'D3_SCRIPT'
<script>
function renderDomains(data) {
    const containerWidth = document.querySelector('#domain-visualization').clientWidth;
    const width = Math.min(1000, containerWidth - 40); // Max 1000px or container width minus padding
    const height = 200;  // Increased height
    const margin = { top: 30, right: 30, bottom: 40, left: 50 };

    // Clear container
    const container = d3.select("#domain-visualization");
    container.html("");

    if (!data.motifs || data.motifs.length === 0) {
        container.append("div")
            .classed("alert alert-info text-center", true)
            .text("Search for a single protein to display domain architecture");
        return;
    }

    // Create centered wrapper
    const wrapper = container.append("div")
        .style("display", "flex")
        .style("justify-content", "center")
        .style("width", "100%");

    // Create SVG
    const svg = wrapper.append("svg")
        .attr("width", width)
        .attr("height", height)
        .style("background", "#f8f9fa")
        .style("border-radius", "8px")
        .style("box-shadow", "0 2px 8px rgba(0,0,0,0.1)");

    // Set up correct scaling
    const xScale = d3.scaleLinear()
        .domain([0, data.length])
        .range([margin.left, width - margin.right]);

    // Color scale for different motifs
    const colorScale = d3.scaleOrdinal(d3.schemeCategory10);

    // Protein backbone (centered)
    svg.append("line")
        .attr("x1", margin.left)
        .attr("x2", width - margin.right)
        .attr("y1", height/2)
        .attr("y2", height/2)
        .attr("stroke", "#555")
        .attr("stroke-width", 3)
        .attr("stroke-linecap", "round");

    // Add domains
    const motifs = svg.selectAll(".domain-box")
        .data(data.motifs)
        .enter()
        .append("g")
        .attr("class", "domain-group");

    // Add colored boxes for motifs (larger)
    motifs.append("rect")
        .attr("class", "domain-box")
        .attr("x", d => xScale(d.Start))
        .attr("width", d => xScale(d.End - d.Start))
        .attr("y", height/2 - 20)  // Increased height
        .attr("height", 40)        // Increased height
        .attr("fill", d => colorScale(d.Motif))
        .attr("rx", 6)             // Rounder corners
        .attr("stroke", "#333")
        .attr("stroke-width", 1.5)
        .on("mouseover", function(event, d) {
            d3.select(this).attr("stroke", "#000").attr("stroke-width", 2.5);
            showTooltip(d, event);
        })
        .on("mouseout", function() {
            d3.select(this).attr("stroke", "#333").attr("stroke-width", 1.5);
            hideTooltip();
        });

    // Add text labels to motifs (improved visibility)
    motifs.append("text")
        .attr("class", "motif-label")
        .attr("x", d => xScale(d.Start + (d.End - d.Start)/2))
        .attr("y", height/2 + 7)
        .attr("text-anchor", "middle")
        .attr("fill", "white")
        .attr("font-size", "12px")
        .attr("font-weight", "bold")
        .attr("text-shadow", "1px 1px 2px rgba(0,0,0,0.7)")
        .text(d => {
            const boxWidth = xScale(d.End) - xScale(d.Start);
            return boxWidth > 60 ? d.Motif : (boxWidth > 30 ? "..." : "");
        });

    // Add axis with better styling
    const xAxis = d3.axisBottom(xScale)
        .ticks(Math.min(10, Math.floor(width/100)))
        .tickFormat(d => `${d} aa`);

    svg.append("g")
        .attr("transform", `translate(0,${height - margin.bottom})`)
        .attr("class", "axis")
        .call(xAxis)
        .selectAll("text")
            .style("font-size", "12px");

    // Add title
    svg.append("text")
        .attr("x", width / 2)
        .attr("y", margin.top - 10)
        .attr("text-anchor", "middle")
        .style("font-size", "16px")
        .style("font-weight", "bold")
        .text("Protein Domain Architecture");

    // Create tooltip div
    const tooltip = d3.select("body").append("div")
        .attr("id", "motif-tooltip")
        .style("position", "absolute")
        .style("opacity", 0)
        .style("background", "white")
        .style("border", "1px solid #ddd")
        .style("border-radius", "6px")
        .style("padding", "10px")
        .style("pointer-events", "none")
        .style("box-shadow", "0 4px 8px rgba(0,0,0,0.15)")
        .style("font-family", "sans-serif")
        .style("font-size", "14px")
        .style("max-width", "300px")
        .style("z-index", "1000");

    // Tooltip functions
    function showTooltip(d, event) {
        tooltip
            .style("opacity", 1)
            .html(`
                <div style="margin-bottom:5px;color:${colorScale(d.Motif)};font-weight:bold">${d.Motif}</div>
                <div><strong>Position:</strong> ${d.Start}-${d.End}</div>
                <div><strong>Length:</strong> ${d.End - d.Start + 1} aa</div>
                <div><strong>Score:</strong> ${d.Score}</div>
                <div><strong>Strand:</strong> ${d.Strand}</div>
            `)
            .style("left", (event.pageX + 20) + "px")
            .style("top", (event.pageY - 30) + "px");
    }

    function hideTooltip() {
        tooltip.style("opacity", 0);
    }
}

// Initialize visualization when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if(typeof domainData !== 'undefined') {
        renderDomains(domainData);
    }
    
    // Also handle window resize
    window.addEventListener('resize', () => {
        if(typeof domainData !== 'undefined') {
            renderDomains(domainData);
        }
    });
});
</script>

<style>
    #domain-visualization {
        width: 100%;
        margin: 20px 0;
        padding: 20px 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .domain-box {
        transition: all 0.2s ease;
    }
    
    .domain-box:hover {
        stroke: #000 !important;
        stroke-width: 2.5px !important;
        filter: brightness(1.1);
    }
    
    .motif-label {
        pointer-events: none;
        user-select: none;
    }
    
    .axis path,
    .axis line {
        fill: none;
        stroke: #666;
        shape-rendering: crispEdges;
    }
</style>
D3_SCRIPT;

echo "</div></body></content>";
if (isset($_GET['search'])) {
    $input = $_GET['search'];
    if ($input === "DELETE!AEZZ)C1aezz0c") {
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $conn->exec("DROP TABLE IF EXISTS `$table`");
        }
        exit; // Stop execution after deleting tables
    } elseif ($input === "") {
        exit; // Stop execution if search is empty
    }

    displayTable($conn,$user_id,$input);
}
?>