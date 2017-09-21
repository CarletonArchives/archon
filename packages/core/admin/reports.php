<?php
/*
*    This is supposed to generate a report. It probably won't, but I'll keep trying.
*    TODO: Polish. Including Installer!
*
*/
isset($_ARCHON) or die();

if(!file_exists('packages/core/admin/reports.csv')){
    die();
}
$query="SELECT * from tblCore_Modules WHERE Script='reports'";
    $res =$_ARCHON->mdb2->query($query);
    if (!PEAR::isError($res)) {
      if(!$row=$res->fetchRow()){
        $queries=array(
        "INSERT IGNORE INTO `tblCore_Phrases` (`ID`, `PackageID`, `ModuleID`, `LanguageID`, `PhraseName`, `PhraseValue`, `RegularExpression`, `PhraseTypeID`) VALUES (NULL, '1', '101', '2081', 'module_name', 'Reports', NULL, '5')",
        "INSERT IGNORE INTO `tblCore_Modules` (`ID`, `PackageID`, `Script`) VALUES ('101', '1', 'reports')"
        );
        foreach($queries as $query){
          $res =$_ARCHON->mdb2->query($query);
          if (PEAR::isError($res)) {
            echo("<br>An error occured with the installation. Retry.");
            $undo=array("DELETE FROM `tblCore_Modules` WHERE `ID`=101 AND `PackageID`=1",
            "DELETE FROM `tblCore_Phrases` WHERE `ModuleID`=101 AND `PackageID`=1",);
            foreach($undo as $query){
              $res=$_ARCHON->mdb2->query($query);
            }
            die();
          }
        }
        $queries=array("ALTER TABLE `tblSubjects_Subjects` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblCollections_Content` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblAccessions_Accessions` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblCollections_Collections` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblDigitalLibrary_DigitalContent` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;",
"ALTER TABLE `tblDigitalLibrary_Files` ADD `dateadded` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        foreach($queries as $run){
          $res2=$_ARCHON->mdb2->query($run);
          if(PEAR::isError($res2)){
            if($res2->getcode()!=-5){
              die('Adding dateadded column failed: '.$res2->getMessage().$res2->getcode());
            }
          }
        }
      }
    }
database_ui_initialize();
function database_ui_initialize()
{
  global $_ARCHON;
  if(!$_REQUEST['f'])
    {
      database_ui_main();
    }
  elseif($_REQUEST['f']=='dialog_report'){
      database_ui_dialog_generate_report();
    }
  elseif($_REQUEST['f']=='install'){
    echo("Install was successful. Click <a href=http://localhost/copyArchon/index.php?p=admin/core/database>Here</a> to return");
  }
  else{
    make_the_report();
  }
}

function database_ui_main()
{
  global $_ARCHON;

  $_ARCHON->AdministrativeInterface->getSection('browse')->disable();
  $_ARCHON->AdministrativeInterface->disableQuickSearch();

  $objModulePhrase = Phrase::getPhrase('header', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
  $strModule = $objModulePhrase ? $objModulePhrase->getPhraseValue(ENCODE_HTML) : 'Archon Module';
  //$strHeaderHelp = $_ARCHON->AdministrativeInterface->createHelpButton('header', $_ARCHON->Package->ID, $_ARCHON->Module->ID);

  $objInstalledPhrase = Phrase::getPhrase('installed', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
  $strInstalled = $objInstalledPhrase ? $objInstalledPhrase->getPhraseValue(ENCODE_HTML) : 'Installed Utilities';

  $objTableInformationPhrase = Phrase::getPhrase('tableinformation', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
  $strTableInformation = $objTableInformationPhrase ? $objTableInformationPhrase->getPhraseValue(ENCODE_HTML) : 'Table Information';

  $objLaunchPhrase = Phrase::getPhrase('launch', $_ARCHON->Package->ID, $_ARCHON->Module->ID, PHRASETYPE_ADMIN);
  $strLaunch = $objLaunchPhrase ? $objLaunchPhrase->getPhraseValue(ENCODE_HTML) : 'Launch';

  $browseSection = $_ARCHON->AdministrativeInterface->getSection('browse');
  $browseSection->disable();

  $arrPackages = $_ARCHON->getAllPackages();
  $generalSection = $_ARCHON->AdministrativeInterface->getSection('general');
  $generalSection->insertRow('info')->insertHTML("This is a page for reports. Reports can be edited in the reports.csv file, found in packages/core/admin. <br/>");

  if(file_exists('packages/core/admin/reports.csv')){
    $inputfile=fopen('packages/core/admin/reports.csv',"r");
    while($stuff=fgetcsv($inputfile)){
      $reportOpts[$stuff[0]]=$stuff[0];
    }
    $makeReport = $generalSection->insertRow('Generate Report')->insertSelect('reportChoices', $reportOpts, array());
    $makeReport->Watch=false;
  }
?>
  <a id="launchMakeReport" href=""><?php echo($strLaunch); ?></a>
  <script type="text/javascript">
  /* <![CDATA[ */
   $(function () {
    $('#launchMakeReport').button({icons:{primary: 'ui-icon-newwin'}, disabled: ($('#reportChoicesInput').val() == "0")})
    .click(function() {admin_ui_launch_make_report(); return false});

    $('#reportChoicesInput').change(function(){
      if($(this).val() != "0"){
        $('#launchMakeReport').button('enable');
      }else{
        $('#launchMakeReport').button('disable');
      }
    })
  });

  function admin_ui_launch_make_report(){
    var reportutility = $('#reportChoicesInput').val();
    if(reportutility != "0"){
      var dialog = $('#dialogmodal');
      var orig_buttons = dialog.dialog('option', 'buttons');
      dialog.dialog('option', 'buttons', {
        Generate: function(){
          $('#dialogloadingscreen').show();
          $('#dialogmodal .relatedselect>*').attr('selected','selected');
          location.href = 'index.php?' + $('#dialogform :input').fieldSerialize();
          $('#dialogform .relatedselect>*').removeAttr('selected');
          $('#dialogloadingscreen').hide();
          $(this).dialog('close');
        },
        Cancel: function(){
          $(this).dialog('close');
          $(this).dialog('option','buttons', orig_buttons);
        }
      });
    admin_ui_opendialog('core','reports', 'report', {reportutility: reportutility});
    }
  }
  /* ]]> */
  </script>

  <?php
  $button = ob_get_clean();
  $generalSection->getRow('Generate Report')->insertHTML($button);
  $_ARCHON->AdministrativeInterface->outputInterface();
}
function database_ui_dialog_generate_report(){
  global $_ARCHON;
  $dialogSection = $_ARCHON->AdministrativeInterface->insertSection('dialogform', 'dialog');
  $_ARCHON->AdministrativeInterface->OverrideSection = $dialogSection;
  $dialogSection->setDialogArguments('form', NULL, 'admin/core/reports', 'makereport');

  $report = $_REQUEST['reportutility'];

  $dialogSection->insertRow('name')->insertInformation('Name', $report);
  $dialogSection->getRow('name')->insertHTML("<input type='hidden' class='reloadparam' name='title' value='{$_REQUEST['reportutility']}' />");
  $dialogSection->insertRow('Description')->insertInformation('desc', "This will run the ".$report." on the previous 31 days, or as specified below.");
  $dialogSection->insertRow('Days Prior')->insertTextField('date', 30, 100);
  $_ARCHON->AdministrativeInterface->outputInterface();
}
function make_the_report(){
  $filename = ($_REQUEST['output']) ? $_REQUEST['output'] : 'csv';
  $inputname = 'reports';
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
    echo "<br>$result";
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
