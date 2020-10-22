<?php

/*
 *  This dashboard is being developed by the DVBrazil Team as a courtesy to
 *  the XLX Multiprotocol Gateway Reflector Server project.
 *  The dashboard is based of the Bootstrap dashboard template.
 * 
 *  This code is further edited by KC1AWV for the M17 Reflector M17-M17
 * 
 *  version 1.1.0 - Bootstrap 4.5
*/

if (file_exists("./include/functions.php")) {
    require_once("./include/functions.php");
} else {
    die("functions.php does not exist.");
}
if (file_exists("./include/config.inc.php")) {
    require_once("./include/config.inc.php");
} else {
    die("config.inc.php does not exist.");
}

if (!class_exists('ParseXML')) require_once("./include/class.parsexml.php");
if (!class_exists('Node')) require_once("./include/class.node.php");
if (!class_exists('xReflector')) require_once("./include/class.reflector.php");
if (!class_exists('Station')) require_once("./include/class.station.php");
if (!class_exists('Peer')) require_once("./include/class.peer.php");
if (!class_exists('Interlink')) require_once("./include/class.interlink.php");

$Reflector = new xReflector();
$Reflector->SetFlagFile("./include/country.csv");
$Reflector->SetPIDFile($Service['PIDFile']);
$Reflector->SetXMLFile($Service['XMLFile']);

$Reflector->LoadXML();

if ($CallingHome['Active']) {

    $CallHomeNow = false;
    if (!file_exists($CallingHome['HashFile'])) {
        $Hash = CreateCode(16);
        $LastSync = 0;
        $Ressource = @fopen($CallingHome['HashFile'], "w");
        if ($Ressource) {
            @fwrite($Ressource, "<?php\n");
            @fwrite($Ressource, "\n" . '$LastSync = 0;');
            @fwrite($Ressource, "\n" . '$Hash     = "' . $Hash . '";');
            @fwrite($Ressource, "\n\n" . '?>');
            @fclose($Ressource);
            @exec("chmod 777 " . $CallingHome['HashFile']);
            $CallHomeNow = true;
        }
    } else {
        include($CallingHome['HashFile']);
        if ($LastSync < (time() - $CallingHome['PushDelay'])) {
            $Ressource = @fopen($CallingHome['HashFile'], "w");
            if ($Ressource) {
                @fwrite($Ressource, "<?php\n");
                @fwrite($Ressource, "\n" . '$LastSync = ' . time() . ';');
                @fwrite($Ressource, "\n" . '$Hash     = "' . $Hash . '";');
                @fwrite($Ressource, "\n\n" . '?>');
                @fclose($Ressource);
            }
            $CallHomeNow = true;
        }
    }

    if ($CallHomeNow || isset($_GET['callhome'])) {
        $Reflector->SetCallingHome($CallingHome, $Hash);
        $Reflector->ReadInterlinkFile();
        $Reflector->PrepareInterlinkXML();
        $Reflector->PrepareReflectorXML();
        $Reflector->CallHome();
    }
} else {
    $Hash = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $PageOptions['MetaDescription']; ?>"/>
    <meta name="keywords" content="<?php echo $PageOptions['MetaKeywords']; ?>"/>
    <meta name="author" content="<?php echo $PageOptions['MetaAuthor']; ?>"/>
    <meta name="revisit" content="<?php echo $PageOptions['MetaRevisit']; ?>"/>
    <meta name="robots" content="<?php echo $PageOptions['MetaAuthor']; ?>"/>

    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title><?php echo $Reflector->GetReflectorName(); ?> Reflector Dashboard</title>
    <link rel="icon" type="image/png" href="/images/icons/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="/images/icons/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/icons/favicon-96x96.png" sizes="96x96">

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug
    <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    -->

    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet">
    <link href="css/navbar-top-fixed.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php

    if ($PageOptions['PageRefreshActive']) {
        echo '
   <script src="./js/jquery-1.12.4.min.js"></script>
   <script>
      var PageRefresh;

      function ReloadPage() {
         $.get("./index.php'.(isset($_GET['show'])?'?show='.$_GET['show']:'').'", function(data) {
            var BodyStart = data.indexOf("<bo"+"dy");
            var BodyEnd = data.indexOf("</bo"+"dy>");
            if ((BodyStart >= 0) && (BodyEnd > BodyStart)) {
               BodyStart = data.indexOf(">", BodyStart)+1;
               $("body").html(data.substring(BodyStart, BodyEnd));
            }
         })
            .always(function() {
               PageRefresh = setTimeout(ReloadPage, '.$PageOptions['PageRefreshDelay'].');
            });
      }';

	if (!isset($_GET['show']) || (($_GET['show'] != 'liveircddb') && ($_GET['show'] != 'reflectors') && ($_GET['show'] != 'interlinks') && ($_GET['show'] != 'livequadnet'))) {
            echo '
      PageRefresh = setTimeout(ReloadPage, ' . $PageOptions['PageRefreshDelay'] . ');';
        }
        echo '

      function SuspendPageRefresh() {
        clearTimeout(PageRefresh);
      }
   </script>';
    }
    if (!isset($_GET['show'])) $_GET['show'] = "";
    ?>
</head>
<body>
<?php if (file_exists("./tracking.php")) {
    include_once("tracking.php");
} ?>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
    <a class="navbar-brand" href="#"><?php echo $Reflector->GetReflectorName(); ?> Reflector</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="navbarCollapse" class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li<?php echo (($_GET['show'] == "users") || ($_GET['show'] == "")) ? ' class="nav-item active"' : ''; ?>><a class="nav-link" href="./index.php">Last Heard</a></li>
            <li<?php echo ($_GET['show'] == "repeaters") ? ' class="nav-item active"' : ''; ?>><a class="nav-link" href="./index.php?show=repeaters">Links (<?php echo $Reflector->NodeCount();  ?>)</a></li>
        </ul>
        <span class="navbar-text px-2">mrefd v<?php echo $Reflector->GetVersion(); ?> - Dashboard v<?php echo $PageOptions['DashboardVersion']; ?></span>
        <span class="navbar-text px-2">Service uptime: <?php echo FormatSeconds($Reflector->GetServiceUptime()); ?></span>
    </div>
</nav>
<main role="main">
    <div class="container-fluid">
        <div class="row">
            <?php 
                /* Do we really want to keep calling home?
                if ($CallingHome['Active']) {
                    if (!is_readable($CallingHome['HashFile']) && (!is_writeable($CallingHome['HashFile']))) {
                        echo '
                            <div class="error">
                                your private hash in ' . $CallingHome['HashFile'] . ' could not be created, please check your config file and the permissions for the defined folder.
                            </div>';
                    }
                }
                */
                switch ($_GET['show']) {
                    case 'users'      :
                        require_once("./include/users.php");
                        break;
                    case 'repeaters'  :
                        require_once("./include/repeaters.php");
                        break;
                    default           :
                        require_once("./include/users.php");
                }

                ?>
        </div>
    </div>
</main>
<footer class="container-fluid">
    <nav class="navbar fixed-bottom">
        <span class="text-muted"><a href="mailto:<?php echo $PageOptions['ContactEmail']; ?>"><?php echo $PageOptions['ContactEmail']; ?></a></span>
            </nav>
</footer>

<!-- Bootstrap core JavaScript
 ================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> -->
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="js/bootstrap.bundle.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug
<script src="js/ie10-viewport-bug-workaround.js"></script>
        -->
</body>
</html>
