<?php
/**
 * Parse the confluence page that is pulled down with the project tracking grid
 */
$html_file = $argv[1];
$status_file = $argv[2];
$full_status_file = $argv[3];

$data_dir = "data";

$DEBUG = false;

// the minimal project version is kept for these projects
$local_file = array(2,3,4,5,8,12);

$project_mapping = array(
	"Automation Stabilization" => "Auto Stab",
	"Partner App" => "Partner",
	"DMP in Whiteboard" => "DMP WB",
	"SMS" => "SMS",
	"Content Management" => "Content",
	"Mobile" => "Mobile",
	"Email Data Storage (DAS)" => "DAS",
	"Blue Hornet Migration" => "Migrate",
	"Response Queue" => "RespQueue",
	"Notification Center" => "Notifier",
	"Mobile SDK" => "Mob SDK",
	"Common Components" => "UI Kit",
	"CI/CD" => "CI/CD",
	"MTA" => "MTA"
);

$color_mapping = array(
	'GOOD'    => 'green',
	'AT RISK' => 'yellow',
	'DANGER'  => 'red',
	'NA'      => 'red',
	'UNKNOWN' => 'red',
	'DELAYED' => 'yellow'
);

// clear out the full file
unlink($full_status_file);

debug("Processing $html_file");

// get the table html
$table = get_table_html($html_file);

$project_html_ar = get_html_by_project($table);

$projects = get_project_info($project_html_ar);

update_file($projects, $status_file);

function update_file($new_string, $status_file) {
	$fp = fopen($status_file, 'w');
	fwrite($fp, $new_string);
	fclose($fp);	
}

function append_to_file($full_status_file, $string){
	$fh = fopen($full_status_file, 'a') or die("can't open file");
	fwrite($fh, $string);
	fclose($fh);
}

/**
 * Reads through the one line project tables and
 * @return an array of project html lines
 */
function get_html_by_project($table_html) {
	// make it multi-line
	// $table_lines = str_replace("</tr><tr>","</tr>\n<tr>", $table_html);
	
	$lines = explode('</tr><tr>', $table_html);

	debug("We found ". count($lines). " lines");

	return $lines;
}

/**
 * @param an array of project
 * @return project info from the html per line
 */
function get_project_info($lines) {
global $local_file;
global $color_mapping;
global $full_status_file;

	debug("About to parse out project info");
	$all_projects = "";
	$projects = "";
	$c = -1;
	foreach ($lines as $line) {
		$c++;
		if ($c == 0) continue;
		debug("\n\n===========\n about to parse [$c]");

		debug("Parsing [$c]: ". $line);

		$project = get_project_name($line);
		$status = get_status($line);
		$next_meeting = get_next_date($line);

		// map status to color
		$color = 'red';
		if (!empty($color_mapping[$status])) {
			$color = $color_mapping[$status];
		}

		append_to_file($full_status_file, "{$project}:{$color}:{$next_meeting}:");

		if (in_array($c, $local_file)) { 
			debug("Status for '{$project}' is '{$status}' and next meeting is '{$next_meeting}'\n");
			$projects .= "{$project}:{$color}:{$next_meeting}:";
		}
	}
	return $projects;
}

/**
 * @param string $html the tr for the row
 * @return the project name out of an html line
 */
function get_project_name($html){
global $project_mapping;

	// split it up by TDs
	$TDs = explode("</td><td", $html);

	if (empty($TDs) || count($TDs) < 5) return "Unknown";
	else {
		$rightRow = $TDs[1];
		// pull out the text
		$name = substr($rightRow,strpos($rightRow,">")+1);
		$name = $project_mapping[$name];
		debug("We found a td of ". $name);
		return $name;
	}
}


/**
 * @param string $html the tr for the row
 * @return the status out of an html line
 */
function get_status($html){
	// data-macro-name="status">GOOD</span>
	$pattern = '/data\-macro\-name\=\"status\"\>([\s\w]+)\<\/span\>/';
	preg_match($pattern, $html, $matches);
	if (isset($matches[1]))	return $matches[1];
	else return "NA";
}

/**
 * @param string $html the tr for the row
 * @return the next meeting date out of an html line
 */
function get_next_date($html){
	$pattern = '/N\:\s*([\d\/\?]+)/';
	preg_match($pattern, $html, $matches);
	if (isset($matches[1]))	return $matches[1];
	else return "NA";
}

/**
 * Finds the status area of the project line
 */
function get_status_area($html) {
	// line breaks added for clarity
	// <span style="color: rgb(0,0,0);">L: 01/03
	// <span class="status-macro aui-lozenge aui-lozenge-success aui-lozenge-subtle conf-macro output-inline" data-hasbody="false" data-macro-name="status">GOOD</span></span></p>
	// <p><span style="color: rgb(0,0,0);">N: ??</span>
	$pattern = '\<span style="color: rgb(.*/data\-macro\-name\=\"status\"\>(\w+)\<\/span\>';
	preg_match($pattern, $html, $matches);
	return $matches[1];
}

/**
 * Reads through the full page html and 
 * @return one big line for the project status table
 */
function get_table_html($file) {
	$file_ar = file($file);
	
	foreach($file_ar as $line) {
		if (strpos($line, 'status-macro') != false) {
			return $line;
		}
	}
}

function debug($mesg, $newline=true){
	global $DEBUG;
	if ($DEBUG) {
		$cut_len = 400;
		if (strlen($mesg) > $cut_len) 
			print substr($mesg, 0, $cut_len) . "... [cut out ". (strlen($mesg) - $cut_len) ." chars]";
		else
			print $mesg;
		if ($newline) { print "\n"; }
	}
}