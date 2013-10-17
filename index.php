<?php

/**
 *
 *        _____                          _____ _        _
 *       /  ___|                        /  ___| |      | |
 *       \ `--.  ___ _ ____   _____ _ __\ `--.| |_ __ _| |_ _   _ ___
 *        `--. \/ _ \ '__\ \ / / _ \ '__|`--. \ __/ _` | __| | | / __|
 *       /\__/ /  __/ |   \ V /  __/ |  /\__/ / || (_| | |_| |_| \__ \
 *       \____/ \___|_|    \_/ \___|_|  \____/ \__\__,_|\__|\__,_|___/
 *
 * ServerStatus : Garder un oeil sur le status de vos serveurs à tout moment.
 * Créé par Hennek, concept adapté de Down for every one or just me, design
 * inspiré de MCstatus (http://xpaw.ru/mcstatus/)
 *
 * @name         ServerStatus
 * @version      1.1
 * @author       Hennek
 * @project      https://github.com/Hennek/ServerStatus
 * @licence      MIT
 *
 */

    header('Content-Type: text/html; charset=utf-8');
    header("Set-Cookie: name=value; httpOnly");
    date_default_timezone_set('Europe/Brussels');
    setlocale(LC_ALL, 'fr_FR');

    define('_VERSION', '1.1');
    define('PATH',     'data/');
    define('CONFIG',   'list.ini');

    /**
     * Script permettant de lire un fichier de config utilisateur
     * @param  string    chemin d'accès au dossier du fichier
     * @return array     tableau contenant chaque ligne du fichier
     */
    function readFileConfig() {
        $values   = array();
        $lineFile = file(PATH . CONFIG);

        foreach($lineFile as $line) {
            // Pour chaque ligne, on casse la chaîne et on stocke
            // le résultat dans des defines si ce n'est pas un commentaire
            if(!preg_match('#\#(.*)#isU', $line)) {
                $infos = explode('=', $line);

                if(count($infos) == 2) {
                    $infos[1] = str_replace('\n', '', $infos[1]);

                    $key      = trim($infos[0]);
                    $value    = trim($infos[1]);
                    $values[] = array($key, $value);
                }
            }
        }

        if(empty($values))
            return 'empty';

        return $values;
    }

    /**
     * Permet d'écrire un fichier.
     * @param  string    le contenu du fichier
     * @return boolean   statut de l'enregistrement
     */
    function addLineConfig($string) {
        return file_put_contents(PATH . CONFIG, utf8_decode($string) . "\n", FILE_APPEND);
    }

    /**
     * Permet d'écrire un fichier.
     * @return boolean   statut de l'opération
     */
    function deleteConfig() {
        return unlink(PATH . CONFIG);
    }

    /**
     * Permet de sécuriser les données reçues
     * @param  string    la chaîne à vérifier
     * @return string    la chaîne vérifiée
     */
    function secure($str) {
        return htmlspecialchars($str);
    }

    /**
     * Ping ... Pong :)
     * @param  string    domaine à tester
     * @param  int       port du serveur (80 par défaut)
     * @param  int       timeout (10 par défaut)
     * @return int       temps en ms
     */
    function pingDomain($domain, $port = 80, $timeout = 10) {
        $starttime = microtime(true);
        $file      = @fsockopen($domain, $port, $errno, $errstr, $timeout);
        $stoptime  = microtime(true);
        $status    = -1;

        if ($file) {
            fclose($file);

            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }

        return $status;
    }


    // Liste des domaines ainsi que le nom à tester
    $listOfServers = array();
    // TODO readConfigFile
    // Syntaxe suivante (en attendant la 1.1) :
    //$listOfServers[] = array(
    //                        'name' => 'Description',
    //                        'url'  => 'www.server.tld',
    //                        'port' => 80
    //                    );
    $listOfServers[] = array(
                            'name' => 'Github',
                            'url'  => 'github.com',
                            'port' => 80
                        );
    $listOfServers[] = array(
                            'name' => 'Google',
                            'url'  => 'google.com',
                            'port' => 80
                        );

    // Préparation de l'affichage
    // Calcul de la largeur des bulles
    $cptServer = count($listOfServers);
    if($cptServer == 1) $width = "width: 100%; *width: 100%;";
    elseif($cptServer == 2) $width = "width: 50%; *width: 50%;";
    elseif($cptServer % 3 == 0 && $cptServer % 4 != 0)  $width = "width: 33%; *width: 33%;";
    else $width = "width: 25%; *width: 25%;";

    // Création des résultats avant l'affichage
    $result = array();
    foreach ($listOfServers as $key => $value) {
        $result[$value['name']] = pingDomain($value['url'], $value['port']);
    }

    // Ajouter une entrée du fichier .ini
    if(isset($_GET['add'])) {
        $add = secure($_GET['add']);

        // TODO
    }

    // Supprimer une entrée du fichier .ini
    if(isset($_GET['delete'])) {
        $delete = secure($_GET['delete']);

        // TODO
    }

    // Modifier une entrée du fichier .ini
    if(isset($_GET['modify'])) {
        $modify = secure($_GET['modify']);

        // TODO
    }

    // Vérifier l'état d'une URL (via formulaire)
    if(isset($_GET['url'])) {
        $url = secure($_GET['url']);

        // TODO
    }

    // Status : API
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
    <meta name="robots" content="noindex"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Server status • Is it down ?</title>

    <link rel="stylesheet" type="text/css" href="static/css/default.css" />

    <!-- Icons -->
    <link rel="shortcut icon" type="image/x-icon" href="static/img/favicon.ico" />

    <link rel="shortcut icon" sizes="1024x1024" href="tpl/img/serverstatusx1024.png">
    <link rel="shortcut icon" sizes="512x512" href="tpl/img/serverstatusx512.png">
    <link rel="shortcut icon" sizes="128x128" href="tpl/img/serverstatusx128.png">
    <link rel="shortcut icon" sizes="114x114" href="tpl/img/serverstatusx114.png">
    <link rel="shortcut icon" sizes="72x72" href="tpl/img/serverstatusx72.png">

    <link rel="apple-touch-icon-precomposed" media="screen and (resolution: 326dpi)" href="static/img/serverstatusx114px.png" />
    <link rel="apple-touch-icon-precomposed" media="screen and (resolution: 163dpi)" href="static/img/serverstatusx57px.png" />
    <link rel="apple-touch-icon-precomposed" media="screen and (resolution: 132dpi)" href="static/img/serverstatusx72px.png" />

    <style>
        .grid .col {
            <?php echo $width; ?>
        }
    </style>
</head>
<body>

    <section>

        <h1>Current status servers</h1>

        <div class="add-server">

        </div>

        <noscript>
            <p>
                Javascript est désactivé. Le site ne pourra pas fonctionner.<br />
                Activez-le pour pouvoir en profiter !
            </p>
        </noscript>

        <div class="grid">
            <?php foreach ($listOfServers as $key => $value): ?>
            <div class="col">
            <div class="info-bulle" id="block-<?php echo $value['name']; ?>">
                <span>
                    <em><?php echo $value['name']; ?></em>
                    <span id="status-<?php echo $value['name']; ?>">Loading ...</span>
                </span>
            </div>
            <div class="info-sup">
                Temps de réponse : <span id="ms-<?php echo $value['name']; ?>">∞</span>ms
            </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer class="dashed clear">
        <div class="footer-right">
            CopyLeft (<a href="https://github.com/Hennek/ServerStatus/blob/master/LICENSE">MIT</a>) <?php echo date('Y', time()); ?> — <a href="https://twitter.com/Hennek_">Hennek</a> — v<?php echo _VERSION; ?><br />
            <a href="https://github.com/Hennek/ServerStatus/issues">Report Issue</a> •
            <a href="https://github.com/Hennek/ServerStatus">Source</a>
        </div>

        Dernière vérification : <span id="time">00:00:00</span>.
        Actualisation dans <span id="timer" class="note">15</span> seconde(s)<br />
        <span id="info">Loading ...</span>
    </footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
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

                if(countDown == 0 && countSlow == 0) info.html(msgOK);
                if(countDown > 0) info.html("Argh, houston, we've got a problem !");
                if(countSlow > 0) info.html("Bon, bon, le réseau est un peu lent !");
                if(countDown > 0 && countSlow > 0) info.html("Je ne sais pas s'il faut paniquer, mais, il y a un vrai problème !");
            }
        });
    }
</script>
</body>
</html>
