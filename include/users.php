<?php

if (!isset($_SESSION['FilterCallSign'])) {
    $_SESSION['FilterCallSign'] = null;
}

if (!isset($_SESSION['FilterModule'])) {
    $_SESSION['FilterModule'] = null;
}

if (isset($_POST['do'])) {
    if ($_POST['do'] == 'SetFilter') {

        if (isset($_POST['txtSetCallsignFilter'])) {
            $_POST['txtSetCallsignFilter'] = trim($_POST['txtSetCallsignFilter']);
            if ($_POST['txtSetCallsignFilter'] == "") {
                $_SESSION['FilterCallSign'] = null;
            }
            else {
                $_SESSION['FilterCallSign'] = $_POST['txtSetCallsignFilter'];
                if (strpos($_SESSION['FilterCallSign'], "*") === false) {
                    $_SESSION['FilterCallSign'] = "*".$_SESSION['FilterCallSign']."*";
                }
            }

        }

        if (isset($_POST['txtSetModuleFilter'])) {
            $_POST['txtSetModuleFilter'] = trim($_POST['txtSetModuleFilter']);
            if ($_POST['txtSetModuleFilter'] == "") {
                $_SESSION['FilterModule'] = null;
            }
            else {
                $_SESSION['FilterModule'] = $_POST['txtSetModuleFilter'];
            }

        }
    }
}

if (isset($_GET['do'])) {
    if ($_GET['do'] == "resetfilter") {
        $_SESSION['FilterModule'] = null;
        $_SESSION['FilterCallSign'] = null;
    }
}

?>

<div class="col-md-9">
    <table class="table table-sm table-striped table-hover">
        <?php
            if ($PageOptions['UserPage']['ShowFilter']) {
                echo '
                    <tr>
                        <th colspan="8">
                            <table width="100%" border="0">
                                <tr>
                                    <td align="left">
                                        <form name="frmFilterCallSign" method="post" action="./index.php">
                                        <input type="hidden" name="do" value="SetFilter" />
                                        <input type="text" class="FilterField" value="'.$_SESSION['FilterCallSign'].'" name="txtSetCallsignFilter" placeholder="Callsign" onfocus="SuspendPageRefresh();" onblur="setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');" />
                                        <input type="submit" value="Apply" class="FilterSubmit" />
                                        </form>
                                    </td>';
                if (($_SESSION['FilterModule'] != null) || ($_SESSION['FilterCallSign'] != null)) {
                    echo '
                        <td><a href="./index.php?do=resetfilter" class="smalllink">Disable filters</a></td>';
                }
                echo '            
                    <td align="right" style="padding-right:3px;">
                        <form name="frmFilterModule" method="post" action="./index.php">
                        <input type="hidden" name="do" value="SetFilter" />
                        <input type="text" class="FilterField" value="'.$_SESSION['FilterModule'].'" name="txtSetModuleFilter" placeholder="Module" onfocus="SuspendPageRefresh();" onblur="setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');" />
                        <input type="submit" value="Apply" class="FilterSubmit" />
                        </form>
                    </td>
                </table>
            </th>
        </tr>';
        }
        ?>
         <tr class="table-center">   
            <th>#</th>
            <th>Flag</th>
            <th>Callsign</th>
            <th>Suffix</th>
            <th>Via / Peer</th>
            <th>Last heard</th>
            <th><img src="./images/ear.png" alt="Listening on" /></th>
         </tr>
        <?php
            function elapsedTime ($time){
            
                $time = time() - $time; // to get the time since that moment
                $time = ($time<1)? 1 : $time;
                $tokens = array (
                    31536000 => 'year',
                    2592000 => 'month',
                    604800 => 'week',
                    86400 => 'day',
                    3600 => 'hour',
                    60 => 'minute',
                    1 => 'second'
                );
            
                foreach ($tokens as $unit => $text) {
                    if ($time < $unit) continue;
                    $numberOfUnits = floor($time / $unit);
                    return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
                }
            
            }
            $Reflector->LoadFlags();
            for ($i=0;$i<$Reflector->StationCount();$i++) {
                $ShowThisStation = true;
                if ($PageOptions['UserPage']['ShowFilter']) {
                    $CS = true;
                        if ($_SESSION['FilterCallSign'] != null) {
                            if (!fnmatch($_SESSION['FilterCallSign'], $Reflector->Stations[$i]->GetCallSign(), FNM_CASEFOLD)) {
                                $CS = false;
                            }
                        }
                    $MO = true;
                    if ($_SESSION['FilterModule'] != null) {
                        if (trim(strtolower($_SESSION['FilterModule'])) != strtolower($Reflector->Stations[$i]->GetModule())) {
                            $MO = false;
                        }
                    }
                    $ShowThisStation = ($CS && $MO);
                }
                if ($ShowThisStation) {
                    echo '
                        <tr class="table-center">
                            <td class="align-middle">';
                                if ($i == 0 && $Reflector->Stations[$i]->GetLastHeardTime() > (time() - 60)) {
                                    echo '<img src="./images/tx.gif" style="margin-top:3px;" height="20"/>';
                                } else {
                                    echo($i + 1);
                                }
                            echo '</td>
                            <td class="align-middle">';
                                list ($Flag, $Name) = $Reflector->GetFlag($Reflector->Stations[$i]->GetCallSign());
                                if (file_exists("./images/flags/" . $Flag . ".svg")) {
                                    echo '<a href="#" class="tip"><img src="./images/flags/' . $Flag . '.svg" class="table-flag" alt="' . $Name . '"><span>' . $Name . '</span></a>';
                                }
                            echo '</td>
                            <td class="align-middle"><a href="https://www.qrz.com/db/' . $Reflector->Stations[$i]->GetCallsignOnly() . '" class="pl" target="_blank">' . $Reflector->Stations[$i]->GetCallsignOnly() . '</a></td>
                            <td class="align-middle">' . $Reflector->Stations[$i]->GetSuffix() . '</td>
                            <td class="align-middle">' . $Reflector->Stations[$i]->GetVia();
                            if ($Reflector->Stations[$i]->GetPeer() != $Reflector->GetReflectorName()) {
                                echo ' / ' . $Reflector->Stations[$i]->GetPeer();
                            }
                            echo '</td>
                            <td>' . @date("d.m.Y H:i", $Reflector->Stations[$i]->GetLastHeardTime()) . '<br />' . elapsedTime($Reflector->Stations[$i]->GetLastHeardTime()) . ' ago</td>
                            <td class="align-middle">' . $Reflector->Stations[$i]->GetModule() . '</td>
                        </tr>';
                }
                if ($i == $PageOptions['LastHeardPage']['LimitTo']) {
                    $i = $Reflector->StationCount() + 1;
                }
            }
        ?>
    </table>
</div>

<div class="col-md-3">
    <?php echo '<a href="' . $PageOptions['Homepage'] . '"><img class="mx-auto d-none d-md-block" src="./images/' . $PageOptions['Logo'] . '" width="50%"></a>'; ?>
    <table class="table table-sm table-striped table-hover">
        <?php 
            $Modules = $Reflector->GetModules();
            sort($Modules, SORT_STRING);
            echo '<tr>';
            for ($i=0;$i<count($Modules);$i++) {
                if (isset($PageOptions['ModuleNames'][$Modules[$i]])) {
                    echo '<th>'.$PageOptions['ModuleNames'][$Modules[$i]];
                    if (trim($PageOptions['ModuleNames'][$Modules[$i]]) != "") {
                        echo '<br />';
                    }
                echo $Modules[$i].'</th>';
                } else {
                    echo '
                    <th>'.$Modules[$i].'</th>';
                }
            }
            echo '</tr><tr>';
            $GlobalPositions = array();
            for ($i=0;$i<count($Modules);$i++) {
                $Users = $Reflector->GetNodesInModulesByID($Modules[$i]);
                echo '<td><table class="table table-hover">';
                $UserCheckedArray = array();
                for ($j=0;$j<count($Users);$j++) {
                    $Displayname = $Reflector->GetCallsignAndSuffixByID($Users[$j]);
                    echo '
                        <tr>
                            <td>'.$Displayname.'</td>
                        </tr>';
                        $UserCheckedArray[] = $Users[$j];
                }
                echo '</table></td>';
            }
            echo '</tr>';
        ?>
    </table>
</div>
</div>