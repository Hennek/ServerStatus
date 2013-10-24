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

/* CONFIGURATION
 ------------------------------------------- */

header('Content-Type: text/html; charset=utf-8');
header("Set-Cookie: name=value; httpOnly");

date_default_timezone_set('Europe/Brussels');
setlocale(LC_ALL, 'fr_FR');

define('_VERSION', '1.1');
define('_AUTHOR',  'Hennek');
define('_LICENSE', 'MIT');

define('PASSWORD', 'foobar');

define('PATH',     'data/');
define('CONFIG',   'list.ini');
define('TIMEOUT',  2);

/**
 * Script permettant de lire un fichier de config utilisateur
 * @param  string    chemin d'accès au dossier du fichier
 * @return array     tableau contenant chaque ligne du fichier
 */
function readFileConfig() {
    $value = parse_ini_file(PATH . CONFIG);
    return !empty($value) ? updateArrayConfig($value) : null;
}

/**
 * Modifie le tableau pour le rendre conforme
 * @param  array    tableau à traiter
 * @return array    nouveau tableau conforme à l'algorithme
 */
function updateArrayConfig($value) {
    for($i = 0, $nbUrl  = count($value['url']); $i < $nbUrl; $i++)
        $listOfServers[] = array(
            'url'   => clearURL($value['url'][$i]),
            'name'  => (isset($value['name'][$i]) && !empty($value['name'][$i])) ? $value['name'][$i] : $value['url'][$i],
            'port'  => (isset($value['port'][$i]) && !empty($value['port'][$i])) ? (int)$value['port'][$i] : 80
        );

    return $listOfServers;
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
 * Permet de nettoyer l'URL avant de faire le ping
 * @param  string   l'url à traiter
 * @return string   l'url démunie des éléments inutiles
 */
function clearURL($str) {
    $str = str_replace("http://","", strtolower($str));
    $str = str_replace("https://","", strtolower($str));
    $str = str_replace("www.","", strtolower($str));

     return $str;
}

/**
 * Permet de retirer tous les accents ainsi que les espaces
 * @param  string   la chaîne de caractères à traiter
 * @return string   la chaîne de caractères dont tous les accents et espaces ont été retiré
 */
function sanitizeName($str) {
    $str = preg_replace('`\s+`', '_', trim($str));
    $str = str_replace("'", "_", $str);
    $str = preg_replace('`_+`', '_', trim($str));

    return strtr($str, "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
                       "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");
}

/**
 * Ping ... Pong :)
 * @param  string    domaine à tester
 * @param  int       port du serveur (80 par défaut)
 * @param  int       timeout (2 par défaut)
 * @return int       temps en ms
 */
function pingDomain($domain, $port = 80, $timeout = TIMEOUT) {
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

/**
 * API
 */
function getInfoDomain($result, $exit = true) {
    $array = array();
    $array['time']   = date('H:i:s', time());
    $array['result'] = $result;

    if($exit) {
        die(json_encode($array));
        exit();
    } else {
        return $array;
    }
}

/**
 *
 *   ######  ########    ###    ########  ########
 *  ##    ##    ##      ## ##   ##     ##    ##
 *  ##          ##     ##   ##  ##     ##    ##
 *   ######     ##    ##     ## ########     ##
 *        ##    ##    ######### ##   ##      ##
 *  ##    ##    ##    ##     ## ##    ##     ##
 *   ######     ##    ##     ## ##     ##    ##
 *
 */

    /**
     * Le formulaire a été envoyé. Deux cas sont possibles :
     * 1. On souhaite ajouter un/des site(s) aux favoris.
     * 2. On souhaite faire un appel à l'API.
     * NOTE : le symbole séparateur est "|", seul le champ url est obligatoire.
     */
    if(isset($_GET['url']) && !empty($_GET['url'])) {
        // Extract
        $value['url']  = secure($_GET['url']);
        $value['name'] = isset($_GET['name']) ? secure($_GET['name']) : '';
        $value['port'] = isset($_GET['port']) ? secure($_GET['port']) : '';

        $value['url']  = explode("|", $value['url']);
        $value['name'] = explode("|", $value['name']);
        $value['port'] = explode("|", $value['port']);

        // Création de la liste des serveurs à traiter
        $listOfServers = updateArrayConfig($value);

        if(isset($_GET['favoris']) && $_GET['favoris'] == "on") {
            // Nouveau(x) site(s) : mettre à jour le fichier de config
            $content = "";
            foreach ($listOfServers as $server) {
                $content .= <<<EOD
[site]
url[]={$server['url']}
name[]={$server['name']}
port[]={$server['port']}

EOD;
            }

            if(!addLineConfig($content))
                header("Location: " . $_SERVER['SCRIPT_NAME'] . "?msg=KO");
            else
                header("Location: " . $_SERVER['SCRIPT_NAME'] . "?msg=OK");
            exit();
        } else {
            // On fait appel à l'API
            foreach ($listOfServers as $server)
                $api[$server['name']] = pingDomain($server['url'], $server['port']);

            header("Location: " . $_SERVER['SCRIPT_NAME'] . "?msg=[@TODO]" . serialize(getInfoDomain($api, false)));
            exit();
        }
    }

    /**
     * Lecture du fichier de configuration. Si le fichier existe, alors on le
     * charge, sinon on utilise une valeur test.
     */
    $listOfServers = readFileConfig();
    if(empty($listOfServers)) {
        $listOfServers[] = array(
            'url'  => 'github.com',
            'name' => 'Github',
            'port' => 80
        );
    }

    /**
     * Création du tableau contenant les résultats de l'opération
     * Partie de ping-pong pour chaque serveur de ce tableau.
     */
    $result    = array();
    $nbServers = count($listOfServers);
    foreach ($listOfServers as $server)
        $result[sanitizeName($server['name'])] = pingDomain($server['url'], $server['port']);

    /**
     * Méthode AJAX qui permet de récupérer le nouveau statut de chaque serveur
     * après chaque 't' secondes où 't' est le nombre de secondes avant le
     * nouvel appel à cette méthode. (Vous suivez ?)
     */
    if(isset($_GET['status']))
        getInfoDomain($result);

    /**
     * Préparation de l'affichage des "bulles d'informations"
     * Calcul de la grille pour la disposition des éléments.
     */
    if($nbServers == 1) $width = "width: 100%; *width: 100%;";
    elseif($nbServers == 2) $width = "width: 50%; *width: 50%;";
    elseif($nbServers % 3 == 0 && $nbServers % 4 != 0)  $width = "width: 33%; *width: 33%;";
    else $width = "width: 25%; *width: 25%;";
?><!DOCTYPE html>
<html lang="fr-FR">
<head>
    <meta charset="utf-8" />
    <meta name="robots" content="noindex"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Server status • Is it down ?</title>

    <link rel="stylesheet" type="text/css" href="static/css/default.css" />

    <!-- Icons -->
    <link rel="shortcut icon" sizes="1024x1024" href="static/img/serverstatusx1024.png">
    <link rel="shortcut icon" sizes="512x512" href="static/img/serverstatusx512.png">
    <link rel="shortcut icon" sizes="128x128" href="static/img/serverstatusx128.png">
    <link rel="shortcut icon" sizes="114x114" href="static/img/serverstatusx114.png">
    <link rel="shortcut icon" sizes="72x72" href="static/img/serverstatusx72.png">

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

        <h1><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">Current status of YOUR servers !</a></h1>

        <noscript>
            <p>
                Javascript est désactivé. Le site ne pourra pas fonctionner.<br />
                Activez-le pour pouvoir en profiter !
            </p>
        </noscript>

        <?php
            if(isset($_GET['msg']) && !empty($_GET['msg'])) {
                echo '<div class="alert">';
                switch (secure($_GET['msg'])) {
                    case 'OK': echo 'L\'opération s\'est parfaitement déroulée !'; break;
                    case 'KO': echo '<span class="alert-error">Une erreur est survenue durant l\'opération, veuillez réessayer ultérieurement</span>'; break;
                    default:   echo secure($_GET['msg']); break;
                }
                echo '</div>';
            }
        ?>

        <div class="grid">
            <?php foreach ($listOfServers as $key => $value): ?>
            <div class="col">
            <div class="info-bulle" id="block-<?php echo sanitizeName($value['name']); ?>">
                <span>
                    <em><?php echo $value['name']; ?></em>
                    <span id="status-<?php echo sanitizeName($value['name']); ?>">Loading ...</span>
                </span>
            </div>
            <div class="info-sup">
                Temps de réponse : <span id="ms-<?php echo sanitizeName($value['name']); ?>">∞</span>ms
            </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="add-server">
            <form method="get" id="testSite">
                <fieldset>
                    <legend>Test on a new server</legend>
                        <label>URL du serveur à tester* :</label>
                        <input type="text" name="url" id="server-url" placeholder="URL du serveur" />
                        <br />

                        <div class="grid">
                            <div class="col-server">
                                <label>Clef à donner à l'url :</label>
                                <input type="text" name="name" id="server-name" placeholder="Nom du site (ex: Google)" />
                                <br />
                            </div>
                            <div class="col-port">
                                <label>Port à tester :</label>
                                <input type="number" name="port" id="server-port" placeholder="80" />
                                <br />
                            </div>
                        </div>

                        <div class="col-favoris">
                            <input type="checkbox" name="favoris" id="favoris" />
                            <label for="favoris" class="label-inline" >Enregistrer le site dans les favoris ?</label>
                        </div>

                        <input type="submit" value="Tester" id="button" /><!-- TODO -->
                </fieldset>
            </form>
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
        <span id="info">Loading ...</span><br />
        En savoir davantage sur l'<a href="https://github.com/Hennek/ServerStatus/blob/master/README.md">API</a> ?
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

            var msgOK    = "Tout va bien dans le meilleur des mondes ! N'est-ce pas merveilleux ?";
            var msgSlow  = "Argh, Houston, we've got a problem !";
            var msgVSlow = "Bon, bon, le réseau est un peu lent !";
            var msgDown  = "Je ne sais pas s'il faut paniquer, mais on dirait qu'il y a un problème !";

            var online   = "En ligne";
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
