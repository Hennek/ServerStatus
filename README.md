# ServerStatus - is it down or not?

![ServerStatus](https://raw.github.com/Hennek/ServerStatus/master/static/img/serverstatusx128.png)

## Introduction

Vous voulez savoir si le site auquel vous tentez d'accéder est toujours disponible ? Qu'il ne soit pas *down* ? Avec **ServerStatus**, c'est simple comme bonjour ! Vous rentrez l'adresse de votre choix et le script vérifiera pour vous. Une **API** est également disponible, elle vous permettra de récupérer les résultats au format `json` et d'obtenir donc les informations en temps réel.

ServerStatus comble parfaitement les petits manques de [Down for every one or just me ?](http://www.downforeveryoneorjustme.com/) puisque vous pouvez, entre autre, enregistrer une liste de site. ServerStatus chargera cette liste lorsque vous vous connecterez et vous donner les temps de réponses de chaque site.

## Fonctionnement de ServerStatus

Le script est écrit en php et ne repose sur aucun système externe ce qui vous permet d'être totalement indépendant.

### Comment installer ServerStatus

Avant de parler du fonctionnent de ServerStatus, il faut passer par l'étape de l'installation..

Liste des prérequis : serveur HTTP (apache, nginx ou autre) ainsi que PHP 5.1+. Le script n'utilise pas de base de données afin d'être le plus modulable possible. Assurez-vous d'avoir les droits d'écriture dans `./data`.

*[Manual]*

1. Télécharger [https://github.com/Hennek/ServerStatus/archive/master.zip](https://github.com/Hennek/ServerStatus/archive/master.zip)
2. Dézipper le fichier téléchargé.
3. Copier le contenu dans le dossier de votre choix sur votre serveur.
4. Renommer le dossier en serverstatus.

*[GIT Clone]*

Dans le dossier de votre choix :

`git clone git://github.com/Hennek/ServerStatus.git serverstatus`


### Comment utiliser ServerStatus

ServerStatus est un outil pour garder un oeil sur vos serveurs en temps réel.

Voici la liste des choses que vous pouvez effectuer :

* Ajouter une adresse dans vos sites favoris, celle-ci sera enregistré dans le fichier `list.ini` dans `./data`.
* Modifier une adresse existante.
* Supprimer une adresse de vos favoris.
* Tester un site sans l'enregistrer dans les favoris.
* Utiliser l'API : récuperer les résultats sous la forme d'un `json`.

### API

#### Résultats retournés

Les résultats retournés sont sous la forme suivante :

    {
	   "time":"15:07:31",
	   "result":{
	      "Github":391,
	      "Google":117
	   }
	}

Voici un exemple de résultat, mais à quoi correspond chacun des champs ?

* `time`: correspond à l'heure à laquelle le résultat est retourné.
* `result` est le tableau comportant les temps de réponse de chaque site.

#### Gestion des erreurs

Si une erreur survient, le temps de réponse pour le site sera égal à `-1`.

## A propos

ServerStatus est un projet open source :

 * **@name** : ServerStatus
 * **@version** : 1.1
 * **@author** : [Hennek](https://twitter.com/Hennek_)
 * **@project** : [https://github.com/Hennek/ServerStatus](https://github.com/Hennek/ServerStatus)
 * **@licence** : [MIT](https://github.com/hennek/serverstatus/blob/master/LICENCE)

### Features

* **Simple** : moins de 500 lignes de code
* Totalement **gratuit** : aucun frais de logiciel, pas d'abonnement de service
* **Open source** : hackez-le, adpatez-le !

### Todo List

Aucun projet n'est parfait, c'est pour ça que cette liste est présente. N'hésitez pas à participer au projet :

* ☑ Pinger un domaine précis ;
* ☑ Prendre en compte le temps de réponse (`ms`) ;
* ☑ Système de favoris ;
* ☑ API : mettre en place une API simple d'utilisation ;
* ☐ Administration (requiert un mot de passe) : ajout, modification et suppression (gestion des favoris) ; 
* ☐ Statistique : taux d'uptime par semaine pour chacun de vos sites ;
* ☐ Multilingue ;
* ☐ Vos idées : si le projet vous plaît, forkez-le !
