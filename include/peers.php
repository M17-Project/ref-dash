<?php

$Result = @fopen($CallingHome['ServerURL']."?do=GetReflectorList", "r");

$INPUT = "";

if ($Result) {

	while (!feof ($Result)) {
		$INPUT .= fgets ($Result, 1024);
	}

	$XML = new ParseXML();
	$Reflectorlist = $XML->GetElement($INPUT, "reflectorlist");
	$Reflectors    = $XML->GetAllElements($Reflectorlist, "reflector");
}

fclose($Result);
?>

<table class="table table-striped table-hover">
	<tr class="table-center">
		<th class="col-md-1">#</th>
		<th class="col-md-2">M17 Peer</th>
		<th class="col-md-2">Last Heard</th>
		<th class="col-md-2">Linked for</th>
		<th class="col-md-2">Protocol</th>
		<th class="col-md-1">Module</th><?php

if ($PageOptions['PeerPage']['IPModus'] != 'HideIP') {
	echo '<th class="col-md-2">IP</th>';
}

?>
 </tr>
<?php

$Reflector->LoadFlags();

for ($i=0;$i<$Reflector->PeerCount();$i++) {
	echo '
	<tr class="table-center">
	<td>'.($i+1).'</td>';

	$Name = $Reflector->Peers[$i]->GetCallSign();
	$URL = '';

	for ($j=1;$j<count($Reflectors);$j++) {
		if ($Name === $XML->GetElement($Reflectors[$j], "name")) {
			$URL  = $XML->GetElement($Reflectors[$j], "dashboardurl");
		}
	}
	if ($Result && (trim($URL) != "")) {
		echo '<td><a href="'.$URL.'" target="_blank" class="listinglink" title="Visit the Dashboard of&nbsp;'.$Name.'" style="text-decoration:none;color:#000000;">'.$Name.'</a></td>';
	} else {
		echo '<td>'.$Name.'</td>';
	}
	echo '
	<td>'.date("d.m.Y H:i", $Reflector->Peers[$i]->GetLastHeardTime()).'</td>
	<td>'.FormatSeconds(time()-$Reflector->Peers[$i]->GetConnectTime()).' s</td>
	<td>'.$Reflector->Peers[$i]->GetProtocol().'</td>
	<td>'.$Reflector->Peers[$i]->GetLinkedModule().'</td>';
	if ($PageOptions['PeerPage']['IPModus'] != 'HideIP') {
		echo '<td>';
		$Bytes = explode(".", $Reflector->Peers[$i]->GetIP());
		$MC = $PageOptions['RepeatersPage']['MasqueradeCharacter'];
		if ($Bytes !== false && count($Bytes) == 4) {
			switch ($PageOptions['PeerPage']['IPModus']) {
				case 'ShowLast1ByteOfIP':
					echo $MC.'.'.$MC.'.'.$MC.'.'.$Bytes[3];
					break;
				case 'ShowLast2ByteOfIP':
					echo $MC.'.'.$MC.'.'.$Bytes[2].'.'.$Bytes[3];
					break;
				case 'ShowLast3ByteOfIP':
					echo $MC.'.'.$Bytes[1].'.'.$Bytes[2].'.'.$Bytes[3];
					break;
				default:
					echo '<a href="http://'.$Reflector->Peers[$i]->GetIP().'" target="_blank" style="text-decoration:none;color:#000000;">'.$Reflector->Peers[$i]->GetIP().'</a>';
			}
		} else {
			$ipstr = $Reflector->Peers[$i]->GetIP();
			$count = substr_count($ipstr, ":");
			if ($count > 1) {
				if (1 == substr_count($ipstr, "::")) { $ipstr = str_replace("::", str_repeat(":", 9 - $count), $ipstr); }
				if (7 == substr_count($ipstr, ":")) {
					echo $MC.':'.$MC.':'.$MC.':'.$MC.':'.$MC.':'.$MC;
					$Bytes = explode(":", $ipstr);
					for( $k=6; $k<8; $k++) { echo (0==strlen($Bytes[$k])) ? ':0' : ':'.$Bytes[$k]; }
				}
			}
		}
		echo '</td>';
	}
	echo '</tr>';
	if ($i == $PageOptions['PeerPage']['LimitTo']) { $i = $Reflector->PeerCount()+1; }
}

?>

</table>
