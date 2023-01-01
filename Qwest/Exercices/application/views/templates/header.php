<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en" id="html">
    <head>
        <meta charset="utf-8">
        <title>Génération</title>
    </head>
    <body>
        <nav id="cssmenu">
        <ul>
            <?php if($this->session->permission === "A"){ ?>
                <div class="haut">
                    <li><a class="titre-menu" href='<?php echo $_SESSION["LHS"] . "admin"; ?>'><span class="titre-qwest">Q?west</span> <span class="titre-HG">HG</span> 1.3.7 Alpha</a></li>
                    <li class='has-sub'><a href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/0"; ?>" id="exercice">Exercices</a>
                        <ul>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/1"; ?>">Seconde</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/2"; ?>">Première ST</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/7"; ?>">Première G</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/3"; ?>">Terminale</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/4"; ?>">Lycée</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/5"; ?>">Collège</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/6"; ?>">Pour tous</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/0"; ?>">Tous les exercices toute classe comprise</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/chapitres-liste?id=0"; ?>">Liste des chapitres</a></li>
                        </ul>
                    </li>
                    <li class='has-sub'><a href="<?php echo $_SESSION["LHS"] . "admin/galerie/I/1"; ?>" id="exercice">Galerie</a>
                        <ul>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/galerie/I/1"; ?>">Images</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/galerie/S/1"; ?>">Sons</a></li>
                            <li><a href="<?php echo $_SESSION["LHS"] . "admin/galerie/V/1"; ?>">Vidéos</a></li>
                        </ul>
                    </li>
                    <li class='has-sub'><a href="<?php echo $_SESSION["LHS"] . "admin/tableau-de-bord"; ?>" id="exercice">Pages de controle</a>
                        <ul>
                            <li><a href='<?php echo $_SESSION["LHS"] . "admin/exercices-prioritaires"; ?>'>Exercices prioritaires</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "admin/liste-des-eleves"; ?>'>Liste des élèves</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "admin/liste-des-classes"; ?>'>Liste des classes</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "admin/tableau-de-bord"; ?>'>Tableau de bord</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "admin/infos-sur-exercices"; ?>'>Informations sur les exercices</a></li>
                        </ul>
                    </li>
                    <li class='has-sub'><a href='<?php echo $_SESSION["LHS"] . "tableau-d-honneur"; ?>'>Tableau d'honneur</a>
                        <ul>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d-honneur/Seconde"; ?>'>Seconde</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d-honneur/Première ST"; ?>'>Première ST</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d-honneur/Première G"; ?>'>Première G</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d-honneur/Terminale"; ?>'>Terminale</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d-honneur"; ?>'>Général</a></li>
                        </ul>
                    </li>
                    <li class='last'><a href="<?php echo $_SESSION["LHS"] . "admin/a-propos"; ?>">A propos</a></li>
                </div>

                <div class="bas">
                    <li class='deconnection'><a href='<?php echo $_SESSION["LHS"] . "deconnection"; ?>'>Se déconnecter</a></li>
                    <li class='pseudo'><a href="<?php echo $_SESSION["LHS"] . "admin/profil"; ?>"><?php echo $_SESSION["pseudo"]; ?></a></li>
                    <li class='message-menu'><a href="<?php echo $_SESSION["LHS"] . "messagerie"; ?>"><?php echo $_SESSION["texteMessage"]; ?></a></li>
                </div>
            
            <?php } else{ ?>
                <div class="haut">
                    <li><a class="titre-menu" href='<?php echo $_SESSION["LHS"] . "connecte"; ?>'><span class="titre-qwest">Q?west </span><span class="titre-HG">HG</span></a></li>
                    <li><a>Exercices</a>
                        <ul>
                            <li class='has-sub'><a href="<?php echo $_SESSION["LHS"] . "connecte/exercices-liste/histoire"; ?>">Histoire</a></li>
                            <li class='has-sub'><a href="<?php echo $_SESSION["LHS"] . "connecte/exercices-liste/géographie"; ?>">Géographie</a></li>
                            <li class='has-sub'><a href="<?php echo $_SESSION["LHS"] . "connecte/exercices-liste/éducation-civique"; ?>">Eduction civique</a></li>
                            <li class='has-sub'><a href="<?php echo $_SESSION["LHS"] . "connecte/exercices-liste/culture-générale"; ?>">Culture générale</a></li>
                            <li class='has-sub'><a href="<?php echo $_SESSION["LHS"] . "connecte/exercices-liste/méthode"; ?>">Méthode</a></li>
                            <li class='has-sub'><a href="<?php echo $_SESSION["LHS"] . "connecte/exercices-liste/tous-les-exercices"; ?>">Tous les exercices toute classe comprise</a></li>
                        </ul>
                    </li>
                    <li class='has-sub'><a href='<?php echo $_SESSION["LHS"] . "tableau-d'honneur/Général"; ?>'>Tableau d'honneur</a>
                        <ul>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d'honneur/Seconde"; ?>'>Seconde</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d'honneur/Première ST"; ?>'>Première ST</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d'honneur/Première L ES"; ?>'>Première L ES</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d'honneur/Terminale"; ?>'>Terminale</a></li>
                            <li><a href='<?php echo $_SESSION["LHS"] . "tableau-d'honneur/Général"; ?>'>Général</a></li>
                        </ul>
                    </li>
                    <li class='last'><a href='<?php echo $_SESSION["LHS"] . "connecte/a-propos"; ?>'>A propos</a></li>
                </div>
                <div class="bas">
                    <li class='deconnection'><a href='<?php echo $_SESSION["LHS"] . "deconnection"; ?>'>Se déconnecter</a></li>
                    <li class='pseudo'><a href="<?php echo $_SESSION["LHS"] . "connecte/profil"; ?>"><?php echo $_SESSION["pseudo"]; ?></a></li>
                    <li class='message-menu'><a href="<?php echo $_SESSION["LHS"] . "messagerie"; ?>"><?php echo $_SESSION["texteMessage"]; ?></a></li>
                </div>
            <?php } ?>
        </ul>
    </nav>

    <div class="bloc-page taille-conteneur">