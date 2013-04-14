<?php

/**
 *
 * ServerStatus : Garder un oeil sur le status de vos serveurs à tout moment
 *
 * Créé par Hennek, design inspiré de MCstatus (http://xpaw.ru/mcstatus/) et
 * de la future version du site de Hennek (http://hennek.be)
 *
 * @name         ServerStatus
 * @version      1.0
 * @author       Hennek
 * @project      https://github.com/Hennek/ServerStatus
 * @licence      Beerware (https://fr.wikipedia.org/wiki/Beerware)
 *
 *
 *    _____                          _____ _        _
 *   /  ___|                        /  ___| |      | |
 *   \ `--.  ___ _ ____   _____ _ __\ `--.| |_ __ _| |_ _   _ ___
 *    `--. \/ _ \ '__\ \ / / _ \ '__|`--. \ __/ _` | __| | | / __|
 *   /\__/ /  __/ |   \ V /  __/ |  /\__/ / || (_| | |_| |_| \__ \
 *   \____/ \___|_|    \_/ \___|_|  \____/ \__\__,_|\__|\__,_|___/
 *
 */

    // FONCTION

    /**
     * Ping ... Pong :)
     * @param $domain(string) - domaine à tester
     * @param $port(int) - port du serveur (80 par défaut)
     * @param $timeout(int) - timeout (10 par défaut)
     * @return Temps en ms
     */
    function pingDomain($domain, $port = 80, $timeout = 10) {
        $starttime = microtime(true);
        $file      = @fsockopen($domain, $port, $errno, $errstr, $timeout);
        $stoptime  = microtime(true);
        $status    = 0;

        // Ping ... ?
        if (!$file) {
            $status = -1;
        } else {
            fclose($file);
            // ... Pong !
            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }

        return $status;
    }


    // Liste des domaines ainsi que le nom à tester
    $listOfServers = array();
    // Syntaxe suivante :
    //   $listOfServers['name']['name']  = 'Description';
    //   $listOfServers['name']['url']   = 'www.server.tld';
    //   $listOfServers['name']['port']  = 80;
    $listOfServers['git']['name'] = 'Github';
    $listOfServers['git']['url']  = 'github.com';
    $listOfServers['git']['port'] = 80;

    // Préparation de l'affichage
    // Calcul de la largeur des bulles
    $cptServer = count($listOfServers);
    if($cptServer == 1)
        $width = "width: 100%; *width: 100%;";
    elseif($cptServer == 2)
        $width = "width: 50%; *width: 50%;";
    elseif($cptServer % 3 == 0 && $cptServer % 4 != 0)
        $width = "width: 33%; *width: 33%;";
    else
        $width = "width: 25%; *width: 25%;";

    // Création des résultats avant l'affichage
    $result = array();
    foreach ($listOfServers as $key => $value) {
        $result[$key] = pingDomain($value['url'], $value['port']);
    }

    // Status - ajax - json
    if(isset($_GET['status'])) {
        $array = array();
        $array['time']   = date('H:i:s', time());
        $array['result'] = $result;

        die(json_encode($array));
        exit();
    }

?><!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="utf-8" />
    <title>Server status • Is it down ?</title>

    <link rel="stylesheet" type="text/css" href="css/default.css" />
    <link href="img/favicon.ico" rel="icon" type="image/x-icon" />

    <style>
        .grid .col {
            <?php echo $width; ?>
        }
    </style>
</head>
<body>

    <section>

        <h1>Current status servers</h1>

        <noscript>
            <p>Javascript est désactivé. Le site ne pourra pas fonctionner.<br />Activez-le pour pouvoir en profiter !</p>
        </noscript>

        <div class="grid">
            <?php foreach ($listOfServers as $key => $value): ?>
            <div class="col">
            <div class="info-bulle" id="block-<?php echo $key; ?>">
                <span>
                    <em><?php echo $value['name']; ?></em>
                    <span id="status-<?php echo $key; ?>">Loading ...</span>
                </span>
            </div>
            <div class="info-sup">
                Temps de réponse : <span id="ms-<?php echo $key; ?>">0</span>ms
            </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer class="dashed clear">
        <div class="footer-right">
            CopyLeft (Beerware) <?php echo date('Y', time()); ?> — <a href="https://twitter.com/Hennek_">Hennek</a> — v1.0<br />
            <a href="https://github.com/Hennek/ServerStatus/issues">Report Issue</a> • <a href="https://github.com/Hennek/ServerStatus">Source</a>
        </div>

        Dernière vérification : <span id="time">00:00:00</span>. Actualisation dans <span id="timer" class="note">15</span> seconde(s)<br />
        <span id="info">Tout va bien ! N'est-ce pas merveilleux ?</span>
    </footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
    update();
    window.setInterval(function() {
        timer = $("#timer");

        if(parseInt(timer.text()) > 0)
            timer.html(parseInt(timer.html()) - 1);

        if(parseInt(timer.text()) <= 0) {
            update();
            timer.html("15");
        }
    }, 1000);

    function update() {
        $.ajax({
            type: "GET",
            url: "?status",
            async: true,
            success:function(json) {
                array  = $.parseJSON(json);
                time   = array.time;
                result = array.result;

                var countDown = 0;
                var countSlow = 0;

                var msgOK    = "Tout va bien ! N'est-ce pas merveilleux ?";
                var msgSlow  = "Argh, houston, we've got a problem !";
                var msgVSlow = "Bon, bon, le réseau est un peu lent !";
                var msgDown  = "Je ne sais pas s'il faut paniquer, mais, il y a un vrai problème !";

                var online   = "Online";
                var slow     = "Slow";
                var vSlow    = "Very slow !";
                var down     = "Down !";

                $.each(result, function (index) {
                    statusInfo = $("#info");
                    blockVar   = $("#block-" + index);
                    statusVar  = $("#status-" + index);
                    info       = $("#info");

                    $("#ms-" + index).html(this);
                    $("#time").html(time);

                    blockVar.removeClass('info-bulle-slow info-bulle-down');
                    statusVar.html(online);
                    statusInfo.html(msgOK);

                    if(this == -1) {
                        blockVar.addClass('info-bulle-down');
                        statusVar.html(down);
                        countDown++;
                    }

                    if(this >= 100) {
                        blockVar.addClass('info-bulle-slow');
                        statusVar.html(slow);
                        countSlow++;
                    }

                    if(this >= 200) {
                        blockVar.addClass('info-bulle-slow');
                        statusVar.html(vSlow);
                        countSlow++;
                    }
                });

                if(countDown == 0 && countSlow == 0)
                    info.html(msgOK);
                if(countDown > 0)
                    info.html("Argh, houston, we've got a problem !");
                if(countSlow > 0)
                    info.html("Bon, bon, le réseau est un peu lent !");
                if(countDown > 0 && countSlow > 0)
                    info.html("Je ne sais pas s'il faut paniquer, mais, il y a un vrai problème !");
            }
        });
    }
</script>
</body>
</html>
