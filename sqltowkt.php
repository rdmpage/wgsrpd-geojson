<?php

// Export WKT to GeoJSON

error_reporting(E_ALL ^ E_DEPRECATED);

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", "root", "", "gis");

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$force = false;

$paths = array();

for ($level = 1; $level <= 4; $level++)
{

	$paths[$level] = array();
	
	switch ($level)
	{
		case 4:
			$sql = 'SELECT level_' . $level . '_na AS name, level' . $level . '_cod AS code, ST_AsText(shape) AS wkt FROM level' . $level;
			break;
			
		case 1:
		case 2:
		case 3:
		default:
			$sql = 'SELECT level' . $level . '_nam AS name, level' . $level . '_cod AS code, ST_AsText(shape) AS wkt FROM level' . $level;
			break;
	}


	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$basename  = 'level' . $level . '/' . $result->fields['code'] . '-' . $result->fields['name'];
		$basename = preg_replace('/[\s\(\)\'\.]/', '_', $basename);
		
		// WKT
		$txt_filename = $basename . '.txt';

		// GeoJSON
		$json_filename = $basename . '.json';
		
		// Keep track of files
		$paths[$level][$result->fields['code']] = $json_filename;
		
		if (!file_exists($txt_filename) || $force)
		{
			file_put_contents($txt_filename, "wkt,\n" . "\"" . $result->fields['wkt'] . "\"");
	
			$command = 'ogr2ogr -f GeoJSON -lco RFC7946=YES -s_srs EPSG:3857 -t_srs EPSG:4326 ';
	
			$command .= $json_filename . ' ' . 'CSV:' . $txt_filename;
	
			echo $command . "\n";
	
			system($command);
	
			// format JSON nicely, and augment with additional data
			$json = file_get_contents($json_filename);
	
			$obj = json_decode($json);
	
			// cleanup and annotate
			foreach ($obj->features as &$feature)
			{
				$feature->properties = new stdclass; // so we delete the large WKT
				$feature->properties->{'LEVEL' . $level . "_COD"} = $result->fields['code'];
				$feature->properties->{'LEVEL' . $level . "_NAM"} = $result->fields['name'];
			}	
			file_put_contents($json_filename, json_encode($obj, JSON_PRETTY_PRINT));
		}
		
		$result->MoveNext();
	}
}

// Map between levelsm codes, and files
print_r($paths);

file_put_contents('filemap.json', json_encode($paths, JSON_PRETTY_PRINT));

?>
