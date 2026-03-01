<?php
function clearUserResults($conn, $user_id) {
    $tables = ["pep_table", "pro_table", "fasta_table"]; // Add all tables that store user results
    foreach ($tables as $table) {
        try{$stmt = $conn->prepare("DELETE FROM $table WHERE user_id = ?");
	$stmt->execute([$user_id]);
	shell_exec("rm -f *{$user_id}*");
	}catch (PDOException $e) {
            echo "Error in $table: " . $e->getMessage() . "<br>";}
    }
}
function displayTable($conn,$user_id,$selected_id=null) {
	maketables($conn);
	if($selected_id=='all'){
$stmt = $conn->prepare("
    SELECT
        MAX(ts.Organism) AS Organism,
	p.SeqName,
        p.MolecularWeight,
	p.ResidueCount,
	p.IsoelectricPoint,
	p.Charge,
	GROUP_CONCAT(DISTINCT pr.Motif SEPARATOR '\n ') AS Motifs
    FROM pep_table p
    LEFT JOIN pro_table pr ON p.SeqName = pr.SeqName
    LEFT JOIN ts_table ts ON p.SeqName = ts.SeqName
    AND p.user_id = pr.user_id 
    WHERE pr.user_id = ? AND ts.Organism IS NOT NULL
    GROUP BY p.SeqName, p.MolecularWeight,p.ResidueCount,p.IsoelectricPoint,p.Charge

");
        $stmt->execute([$user_id]);
     }else{
	     if (!is_array($selected_id)) {
		     $selected_id = explode(',', $selected_id);
		     $selected_id = array_map('trim', $selected_id);
	     }
	     if (empty($selected_id)) {
    echo "<p>No IDs selected!</p>";
    return;
	     }
    $IDlist = implode(',', array_fill(0, count($selected_id), '?'));
	     if(count($selected_id)==1){
	    $stmt = $conn->prepare("SELECT
	ts.Organism,
	p.SeqName,
        p.MolecularWeight,
        p.ResidueCount,
	p.ResidueWeight,
	p.IsoelectricPoint,
	p.Charge,
	p.ExtinctionReduced,
	p.Probability_pos_neg,
	GROUP_CONCAT(DISTINCT pr.Motif SEPARATOR '\n ') AS Motifs
    FROM pep_table p
    LEFT JOIN pro_table pr ON p.SeqName = pr.SeqName 
    AND p.user_id = pr.user_id
    LEFT JOIN ts_table ts ON p.SeqName = ts.SeqName
    WHERE p.SeqName IN ($IDlist) AND pr.user_id = ?
    GROUP BY p.SeqName, ts.Organism,p.MolecularWeight,p.ResidueCount,p.ResidueWeight, p.IsoelectricPoint,p.Charge,p.ExtinctionReduced,p.Probability_pos_neg");

	     } elseif(count($selected_id)==2 AND $selected_id[0]=="MOTIF")
	     {$stmt = $conn ->prepare("SELECT  
		     MAX(ts.Organism) as Organism,
		     pr.SeqName,
		     pr.Start,
		     pr.End,	
		     pr.Score,
		     pr.Strand,
		     pr.Motif   FROM pro_table pr
			 LEFT JOIN ts_table ts ON pr.SeqName = ts.SeqName
	    WHERE pr.SeqName IN ($IDlist) AND pr.user_id=?
	    GROUP BY Organism,pr.SeqName,pr.Start,pr.End,pr.Score,pr.Strand,pr.Motif");
	     } elseif(count($selected_id)==2 AND $selected_id[0]=="ALIGNMENT")
             {$stmt = $conn ->prepare("SELECT
                     MAX(ts.Organism) as Organism,
                     fa.SeqName,
		     MAX(fa.Sequence) as Alignment
			FROM fasta_table fa
                         LEFT JOIN ts_table ts ON fa.SeqName = ts.SeqName
	    WHERE fa.SeqName IN ($IDlist) AND fa.user_id=?
	    GROUP BY Organism,fa.SeqName,fa.Sequence");
             }
	     else{
	$stmt = $conn->prepare("SELECT ts.Organism,p.SeqName,
        p.MolecularWeight,
        p.ResidueCount,
        p.IsoelectricPoint,
	p.Charge,
	GROUP_CONCAT(DISTINCT pr.Motif SEPARATOR '\n ') AS Motif
	    FROM pep_table p
	    LEFT JOIN pro_table pr ON p.SeqName = pr.SeqName AND pr.user_id=p.user_id
LEFT JOIN ts_table ts ON pr.SeqName = ts.SeqName	
WHERE p.SeqName IN ($IDlist) AND pr.user_id=?
	GROUP BY p.SeqName, p.MolecularWeight,p.ResidueCount,p.IsoelectricPoint,p.Charge,ts.Organism
    ");
	     };}
	     $params = is_array($selected_id) ? array_merge($selected_id, [$user_id]) : [$user_id];
	     $stmt->execute($params);
     $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
     if(!$results)
     {echo "<p>No results!</p>";return;}
    echo "<div class='table-container'>";
    echo "<table class='protein-table'>";
    echo "<thead><tr>";
    foreach (array_keys($results[0]) as $column) {
        echo "<th>" . htmlspecialchars($column) . "</th>";
    }
    echo "</tr></thead><tbody>";
    
foreach ($results as $row) {
    echo "<tr>";
    foreach ($row as $key => $cell) {
        $cellValue = (string)$cell; // Convert null to empty string
        if ($key === 'Motifs') {
            echo "<td class='motif-cell'>" . nl2br(htmlspecialchars($cellValue)) . "</td>";
        } else {
            echo "<td>" . htmlspecialchars($cellValue) . "</td>";
        }
    }
    echo "</tr>";
}
    
    echo "</tbody></table></div>";
}

function generateUUID() {
    return bin2hex(random_bytes(8)); 
  }
  

function uploadtsv($filepath,$conn,$table_name,$columns,$user_id){
   $column_list = implode(", ", $columns);  // Convert array to a column string
   $temp_table = $table_name . "_temp";
   $conn->exec("CREATE TEMPORARY TABLE IF NOT EXISTS $temp_table LIKE $table_name;");   
   #$columns[] = "user_id"; 
   $column_list = implode(", ", $columns); 
   $query = "
      	LOAD DATA LOCAL INFILE :filepath
        INTO TABLE $temp_table
        FIELDS TERMINATED BY '\t'
        LINES TERMINATED BY '\n'
        IGNORE 1 LINES
        ($column_list)
    ";
    #$conn->exec("ALTER TABLE $temp_table ADD COLUMN user_id VARCHAR(255);");
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":filepath", $filepath, PDO::PARAM_STR);
    $stmt->execute();
    $conn->exec("UPDATE $temp_table SET user_id = '$user_id';");
    $column_list_with_user = implode(", ", array_merge($columns, ["user_id"]));
    $insert_query = "
            INSERT INTO $table_name ($column_list_with_user)
            SELECT $column_list_with_user FROM $temp_table
            WHERE NOT EXISTS (
            SELECT 1 FROM $table_name 
            WHERE $table_name.SeqName = $temp_table.SeqName 
            AND $table_name.user_id = '$user_id'
        );
        ";
	$conn->exec($insert_query);
        $conn->exec("DROP TEMPORARY TABLE IF EXISTS $temp_table;");
}

function uploadfasta($filepath,$conn,$user_id){
  $data = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  $seq_name = "";
  $sequence = "";
  #$conn->exec("ALTER TABLE fasta_table ADD COLUMN IF NOT EXISTS user_id VARCHAR(255);");
  $stmt = $conn->prepare("
        INSERT INTO fasta_table (SeqName, Sequence, user_id)
	Select ?, ?, ? from dual 
            WHERE NOT EXISTS (
            SELECT 1 FROM fasta_table WHERE SeqName = ? AND user_id = ?
        )
    ");
  foreach ($data as $line) {
        if (strpos($line, ">") === 0) { // Header line
            if ($seq_name) {
               $stmt->execute([$seq_name, $sequence,$user_id, $seq_name, $user_id]); // Save previous sequence
            }
            $seq_name = explode(" ",substr($line, 1))[0]; // Remove '>'
            $sequence = "";
        } else {
            $sequence .= trim($line);
        }
    }
     if ($seq_name) {
        $stmt->execute([$seq_name, $sequence,$user_id, $seq_name, $user_id]); // Save last sequence
    }
}

function maketables($conn){
  $tables = [
        "CREATE TABLE IF NOT EXISTS pep_table (
            SeqName VARCHAR(255),
            MolecularWeight FLOAT NOT NULL,
            ResidueCount INT NOT NULL,
            ResidueWeight FLOAT,
	    Charge FLOAT,
	    IsoelectricPoint FLOAT,
            ExtinctionReduced INT,
            ExtinctionBridges INT,
            ReducedMgMl INT,
            BridgeMgMl INT,
	    Probability_pos_neg FLOAT,
	    user_id VARCHAR(255)     
            )",

        "CREATE TABLE IF NOT EXISTS fasta_table (
            SeqName VARCHAR(255),
	    Sequence TEXT NOT NULL,
	    user_id VARCHAR(255)
        )",
	"CREATE TABLE IF NOT EXISTS ts_table(
SeqName VARCHAR(255),
Organism VARCHAR(255),
Definition VARCHAR(255),
Length INT,
user_id VARCHAR(255))",
        "CREATE TABLE IF NOT EXISTS pro_table (
            SeqName VARCHAR(255),
            Start INT NOT NULL,
	    End INT NOT NULL,
	    Score INT NOT NULL,
	    Strand VARCHAR(50),
	    Motif TEXT,
	    user_id VARCHAR(255)
        )"];
  foreach ($tables as $sql) {
	  $conn->exec($sql);
      }
  }
?>
