<?php
/*
*	This is supposed to generate a report. It probably won't, but I'll keep trying.
*
*/
isset($_ARCHON) or die();
$filename = ($_REQUEST['output']) ? $_REQUEST['output'] : 'csv';
$inputname = ($_REQUEST['input']) ? $_REQUEST['input'] : 'reports';
$title = ($_REQUEST['title']) ? $_REQUEST['title'] : NULL;
$date = ($_REQUEST['date']) ? $_REQUEST['date'] : 31;
if(file_exists('packages/core/admin/'.$inputname.'.csv') && !is_null($title)){
	$inputfile=fopen('packages/core/admin/'.$inputname.'.csv',"r");
	$exit=false;
	$count=0;
	while($count=$count+1 && !$exit && ($stuff=fgetcsv($inputfile))){
		$exit=$title==$stuff[0];
	}
	if($exit==1){
		$query=$stuff[1];
		$query=str_replace("[%date%]",$date,$query);

		printQuery($title,$query);
	}
}
function printQuery($title,$q) {
	$out=runQuery($q);
	header('Content-type: text; charset=UTF-8');
	header('Content-Disposition: attachment; filename="'.$title.'.csv"');
	foreach($out[0] as $key=>$val){
		print("\"$key\",");
	}
	print("\n");
	foreach($out as $item){
		foreach($item as $key=>$val){
			print("\"$val\",");
		}
		print("\n");
	}
	return 1;
}
function runQuery($q) {
  global $_ARCHON;

  // Security Note:
  // It would be more secure to use the mdb2->prepare() function followed by
  // exec(), however with the checks already in place, it did not seem
  // completely necessary to be extra security conscience about this utility.
  $result = $_ARCHON -> mdb2 -> query($q);
  if(PEAR::isError($result)) {
    echo "ERROR: Unable to index ";
    echo ". There is a problem with the following query:<br><br>";
    echo "$q";
    exit(1);
  }
  if($result->numRows()==0){
	echo "There is nothing to report.";
	exit(1);
  }
  $res = $result -> fetchAll();

  $result -> free();

  return $res;
}












?>
