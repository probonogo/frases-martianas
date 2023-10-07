<?php
//download and install ADOdb from here: http://sourceforge.net/projects/adodb/
// With XAMPP ADODB is installed... http://www.apachefriends.org/en/xampp.html

include_once 'adodb/adodb-errorpear.inc.php';
include_once 'adodb/adodb.inc.php';
//include_once 'adodb/tohtml.inc.php';

//Examples
			$vtypeSource = 'firebird';
			$vhostnameSource = '127.0.0.1';
			$vDBSource = 'C:\xampp\databases\COLLMAN.GDB';
			$vuserSource = 'SYSDBA';
			$vpswdSource = 'masterkey';
			$vlocaleSource = '';
			
			$vtypeSource =  'mysql';  //crear la base de datos con la herramienta que tenemos para ello ya...
			$vhostnameSource = '127.0.0.1';
			$vDBSource =  'jbpr';
			$vuserSource ='root';
			$vpswdSource =  '';
			$vlocaleSource ='';

//Configuration
			
	$vtypeSource = 'access';
	$vhostnameSource = '127.0.0.1';
	$vDBSource = 'C:\xampp\htdocs\marti\MARTI.MDB';
	$vuserSource = '';
	$vpswdSource = '';
	$vlocaleSource = '';	
	$vutf8encode = false;
	$vtotallinespersession = 1000;

	$vuser = (isset($_GET['vuser'])) ? $_GET['vuser'] : "";
	$vpswd = (isset($_GET['vpswd'])) ? $_GET['vpswd'] : "";
	$vdb = (isset($_GET['vdb'])) ? stripcslashes($_GET['vdb']) : "";
	$vhost = (isset($_GET['vhost'])) ? stripcslashes($_GET['vhost']) : "localhost";
	$vtype = (isset($_GET['vtype'])) ? $_GET['vtype'] : "mysql";	
	
	if ( 
		(isset($_GET['vuser'])) &&
		(isset($_GET['vpswd'])) &&
		(isset($_GET['vdb'])) &&
		(isset($_GET['vtype'])) &&
		(isset($_GET['vhost'])) 
		)
		{
		$vtypeSource = $_GET['vtype']; //access, firebird
		$vhostnameSource = stripcslashes($_GET['vhost']);
		$vDBSource = stripcslashes($_GET['vdb']);
		$vuserSource = $_GET['vuser'];
		$vpswdSource = $_GET['vpswd'];
		$vlocaleSource = '';
		}
	else
		{
	?>
		<p><b>Step 1. Database conection parameters:</b></p>
		<form method="get">
			<table border="1">
			<tr><td>Usuario:</td><td><input name="vuser" type="text" value="<?php echo $vuser; ?>" /></td><tr>
			<tr><td>Password:</td><td><input name="vpswd" type="text" value="<?php echo $vpswd; ?>"/></td><tr>
			<tr><td>Nombre de la base de datos:</td><td><input name="vdb" type="text" value="<?php echo $vdb; ?>"/></td><tr>
			<tr><td>DB Host:</td><td><input name="vhost" type="text" value="<?php echo $vhost; ?>"/></td><tr>
			<tr><td>DB Type:</td><td>
				<select name="vtype" onChange="javascript:if (vtype.value=='access') alert('access');">
				<option value="mysql" <?php echo ($vtype=="mysql")? "selected=\"selected\"" : ""; ?>>mysql</option>
				<option value="access" <?php echo ($vtype=="access")? "selected=\"selected\"" : ""; ?>>access</option>
				<option value="firebird" <?php echo ($vtype=="firebird")? "selected=\"selected\"" : ""; ?>>firebird</option>
				</select>
			</td><tr>
			<tr><td></td><td><input type="submit" value="Send"/>
			<input name="step" type="hidden" value="2"/></td><tr>
			</table>
		</form>
	<?php
		exit;
		//echo "parametros no encontrados...";
		}



	$ADODB_COUNTRECS=false; //no se puede usar la funcion $rec->RecordCount().
	$dbSource = &ADONewConnection($vtypeSource);
	$dbDest = &ADONewConnection("mysql");
	$dbSource->debug = false;
	if($vtypeSource == "odbc")
		{

		if(PERSISTANT_CONNECTIONS)
			{
			$dbSource->PConnect($vDBSource, $vuserSource,$vpswdSource, $vlocaleSource);
			}
		else 	$dbSource->Connect($vDBSource, $vuserSource,$vpswdSource, $vlocaleSource);
		}
	if($vtypeSource == "access")
		{

		if(PERSISTANT_CONNECTIONS)
			{
			//$dbSource->PConnect($s_connection['database'], $s_connection['user'],$s_connection['pswd'], $s_connection['locale']);
			$dbSource->PConnect("Driver={Microsoft Access Driver (*.mdb)};Dbq=".$vDBSource.";Uid=".$vuserSource.";Pwd=".$vpswdSource.";");
			}
		else 	
			{
			//$dbSource->Connect($s_connection['database'], $s_connection['user'],$s_connection['pswd'], $s_connection['locale']);
			$dbSource->Connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=".$vDBSource.";Uid=".$vuserSource.";Pwd=".$vpswdSource.";");
			}
		}
	else if (($vtypeSource == "ibase") or ($vtypeSource == "firebird"))
		{
		if(PERSISTANT_CONNECTIONS)
			{
			$dbSource->PConnect($vhostnameSource.":".$vDBSource,$vuserSource,$vpswdSource);
			}
		else 	{
			$dbSource->Connect($vhostnameSource.":".$vDBSource,$vuserSource,$vpswdSource);
			}
		}
	else 	{
		if(PERSISTANT_CONNECTIONS)
			{
			$dbSource->PConnect($vhostnameSource,$vuserSource,$vpswdSource, $vDBSource,$vlocaleSource);
			}
		else $dbSource->Connect($vhostnameSource,$vuserSource,$vpswdSource,$vDBSource,$vlocaleSource);
		}
		
		$vtables = array();		
		$vtables = $dbSource->MetaTables('TABLES');

	if ( 
		( (isset($_GET['step'])) && $_GET['step']!="3")
		)
		{
		?>
		<p><b>Step 2. Select the tables to export:<hr /></b></p>

		<form name="formcreate" method="get">
		<input name="vuser" type="hidden" value="<?php echo $vuser; ?>" />
		<input name="vpswd" type="hidden" value="<?php echo $vpswd; ?>"/>
		<input name="vdb" type="hidden" value="<?php echo $vdb; ?>"/>
		<input name="vhost" type="hidden" value="<?php echo $vhost; ?>"/>
		<input name="vtype" type="hidden" value="<?php echo $vtype; ?>"/>
		<input name="markall" type="hidden" value=""/>
		
		<table border="1">
	<?php
		$vchecked = (isset($_GET['markall'])) ? "checked" : "";
		
		foreach ($vtables as $vtable)
			{
			echo "<tr><td><input name=\"vtables[]\" type=\"checkbox\" value=\"$vtable\" $vchecked ></td><td> $vtable</td><tr>";
			}
		echo "<tr><td></td><td><a href=\"javascript: void(0)\" onclick=\"javascript: document.formcreate.step.value=2; document.formcreate.markall.value=1; document.formcreate.submit();\"> Import all!</a></td><tr>";
			
		?>			
			<tr><td></td><td><input type="submit" value="Send"/>
			<input name="step" type="hidden" value="3"/></td><tr>
			</table>
		</form>
	<?php
		exit;
		}
	else
		{
		echo "<p><b>Step 3. Creating SQL file:<hr /></b></p>";
		//tables to process...
		//print_r($_GET);
		if ( isset($_GET["alltables"]) )
			{
			$vtables = array();		
			$vtables = $dbSource->MetaTables('TABLES');
			}
		else
			{
			$vtables = $_GET['vtables'];
			}
		//print_r($vtables);
		//exit;
		}



$gestor = fopen("out.sql", 'w');
foreach ($vtables as $vtable)
	{
	echo "$vtable...<br />";
	$sql = "SELECT * FROM $vtable";
	$vout = "";

	$dbSource->SetFetchMode(ADODB_FETCH_ASSOC);
	$rec = &$dbSource->Execute($sql);
	if (!$rec) 
		print $dbSource->ErrorMsg();
	else
		{
		
		$vout = "\n\nCREATE TABLE `$vtable` ( \n";
		$first = true;
		
		for ($i=0, $max=$rec->FieldCount(); $i < $max; $i++) 
			{
			$fld = $rec->FetchField($i);
			$type = $rec->MetaType($fld->type);
			$vfield = $fld->name;
			
			//print_r($fld); echo " ... $vfield '$type'<br />";
			
			if (!$first) { 	$vout .= ", \n"; }
			$first = false;	
			
			if ($type=="C") { $vout .=" `$vfield` VARCHAR(".($fld->max_length).") NULL "; }
			else if ($type=="D") { $vout .=" `$vfield` DATE NULL "; }
			else if ($type=="X") { $vout .=" `$vfield` BLOB NULL "; }
			else if ($type=="T") { $vout .=" `$vfield` TIME NULL "; }
			else if ($type=="L") { $vout .=" `$vfield` BOOL NULL "; }
			else if ($type=="N") { $vout .=" `$vfield` DECIMAL NULL "; }
			else if ($type=="I") { $vout .=" `$vfield` INT NULL "; }
			else if ($type=="R") { $vout .=" `$vfield` INT NULL "; }
			else { $vout .= "`$vfield` varchar(254) default NULL "; }
			}	
		
		
		$vout .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1; \n";
		$vout .= "\n";
		
		$first = true;	
		//echo nl2br($vout);
		fwrite($gestor, $vout);
		$vlinespersession = 0;
		
		while (!$rec->EOF) {
			$record = array();
			foreach ($rec->fields as $vfield => $vvalue)
				{
				$vvalue = (addslashes($vvalue));
				if ($vutf8encode) { $vvalue = (utf8_encode($vvalue)); }
				$record[$vfield] = $vvalue;
				}
			$vout = $dbDest->GetInsertSQL($rec, $record, 1)."; \n";			
			
			$rec->MoveNext();
			$first = false;	
			
			//echo nl2br(htmlentities($vout));
			fwrite($gestor, $vout);
			}

		//echo nl2br(htmlentities($vout));
		fwrite($gestor, $vout);
		}
	}

fclose($gestor);

//echo "<hr />"; echo "<pre>"; print_r($dbSource); echo "</pre>";

?>
