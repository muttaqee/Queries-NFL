<!DOCTYPE html>

<html>
<head>
	<title>COMP 426 - A3</title>
</head>

<body>
	<?php 
		// Problems flag
		$flag = false;
	
		// Connect to database
		$conn = new mysqli("classroom.cs.unc.edu", "muttaqee", "llllllllllll", "muttaqeedb");
		if ($conn->connect_errno) {
			echo "Database connection error.<br><br>" . $conn->connect_errno;
		} else {
			echo "Connected to database.<br><br>";
		}
		
		// Read and insert rows from file into database
		$readFile = fopen("a3-data.txt", "r");
		if ($readFile) {
			while (($line = fgets($readFile)) != false) {
				// Break line into individual values; store in array
				$row = explode(" ", trim($line));
				$size = sizeof($row);
				
				// Process line
				if ($size === 6 || $size === 8) {
					// Form query string
					$query = ($size === 6) ? "INSERT INTO a3
						(first_name, last_name, team_name, opposing_team, date, passing_rushing_fieldgoal)
						VALUES (" : "INSERT INTO a3
						(first_name, last_name, team_name, opposing_team, date, passing_rushing_fieldgoal, qb_first_name, qb_last_name)
						VALUES (";
					for ($i = 0; $i < $size - 1; $i++) {
						$query = $query . "\"" . $row[$i] . "\"" . ", "; // All values except last
					}
					$query = $query . "\"" . $row[$size-1] . "\"" . ");"; // Last value and close query
					echo $query . "<br>";
					
					// Insert into database
					$result = $conn->query($query);
					if ($result) {
						echo "Inserted row:<br>";
					} else {
						echo "DID NOT INSERT ROW:</br>";
						$flag = true;
					}
				} else {
					echo "ENCOUNTERED PROBLEM LINE:<br>";
					$flag = true;
				}
				echo $line . "<br><br>";
			}
			echo "<br>";
		} else {
			echo "Error opening read-from file.<br><br>";
			exit();
		}
		
		// Perform queries
		if ($problems) {
			echo "PROBLEMS ENCOUNTERED. WILL NOT PERFORM QUERIES.<br>";
			exit();
		} else {
			echo "PERFORMING QUERIES.<br><br>";
			
			// Open text file (to submit; NOTE: writing may not actually work in Codiad. Utilizing alternative method for submission)
			$writeFile = fopen("A3.txt", "w") or die("Error opening write-to file.");
			$altString = "";
			
			// Query 1 of 3
			$query = 'SELECT COUNT(*)
FROM a3
WHERE (
    (
    	(
        	first_name LIKE "Peyton"
    		AND last_name LIKE "Manning"
        ) AND (
            passing_rushing_fieldgoal LIKE "passing"
            OR passing_rushing_fieldgoal LIKE "rushing"
        )
    ) OR (
        qb_first_name LIKE "Peyton"
        AND qb_last_name LIKE "Manning"
    )
) AND opposing_team LIKE "Miami";';
			$altString .= $query . "<br>";
			$result = $conn->query($query);
			$result_1 = $result->fetch_array();
			$altString .= $result_1[0] . "<br>";
			fwrite($writeFile, $query . "\n" . $result_1[0] . "\n");
			
			// Query 2 of 3
			$altString .= $query = 'SELECT A.opposing_team, A.date
FROM a3 A
WHERE A.team_name LIKE "Tennessee"
AND (
    (
        (SELECT COUNT(*) * 3 FROM a3 B WHERE B.date = A.date AND B.team_name LIKE "Tennessee" AND B.passing_rushing_fieldgoal LIKE "fieldgoal")+(SELECT COUNT(*) * 7 FROM a3 C WHERE C.date = A.date AND C.team_name LIKE "Tennessee" AND (C.passing_rushing_fieldgoal LIKE "passing" OR C.passing_rushing_fieldgoal LIKE "rushing"))
    ) > (
        (SELECT COUNT(*) * 3 FROM a3 D WHERE D.date = A.date AND D.opposing_team LIKE "Tennessee" AND D.passing_rushing_fieldgoal LIKE "fieldgoal")+(SELECT COUNT(*) * 7 FROM a3 E WHERE E.date = A.date AND E.opposing_team LIKE "Tennessee" AND (E.passing_rushing_fieldgoal LIKE "passing" OR E.passing_rushing_fieldgoal LIKE "rushing"))
    )
)
GROUP BY A.date;';
			$altString .= "<br>";
			$result = $conn->query($query);
			fwrite($writeFile, $query . "\n");
			while($row = $result->fetch_assoc()){
    			$altString .= $row["opposing_team"] . " " . $row["date"] . "<br>";
				fwrite($writeFile, $row["opposing_team"] . " " . $row["date"] . "\n");
			}
			
			// Query 3 of 3
			$altString .= $query = 'SELECT COUNT(*)
FROM a3
WHERE first_name LIKE "Reggie"
AND last_name LIKE "Bush"
AND passing_rushing_fieldgoal LIKE "rushing"
AND date LIKE "____-10-__";';
			$altString .= "<br>";
			$result = $conn->query($query);
			$result_3 = ($result->fetch_array());
			$altString .= $result_3[0] . "<br>";
			fwrite($writeFile, $query . "\n" . $result_3[0] . "\n");
			
			// Close write-to file
			fclose($writeFile);
			
			// Text for submission (put in a3.txt)
			echo $altString;
		}
	?>
</body>
</html>