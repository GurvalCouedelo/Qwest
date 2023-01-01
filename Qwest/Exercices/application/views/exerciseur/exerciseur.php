<script src='https://cdn.tiny.cloud/1/l6wch4sqm21ilpc5nj95qint6s1be80zit853jaat1f3az8b/tinymce/5/tinymce.min.js' referrerpolicy="origin"></script>
<script>
    tinymce.init({
      selector: '#tiny'
    });
  </script>

<header class="banderole-titre"><h1><?php echo $exercice->titre; ?></h1></header>
<div class="conteneur-exerciseur">
    <nav id="menu-questions">
        <a class="lien-exerciseur" href="<?php echo $_SESSION["LHS"] . "exercice/" . $exercice->id; ?>">Tester l'exercice</a>
        <?php 
            if($exercice->publie == "0"){
                ?>
                    <a id="bouton-disponibilite"><span class="masquer">Masquer l'exercice</span></a>
                <?php
            }
            else{
                ?>
                    <a id="bouton-disponibilite"><span class="publier">Publier l'exercice</span></a>
                <?php
            }
        
        
        ?>
        
        <button id="bouton-fenetre">Créer une question +</button>
        <ul class="liste-questions">
            
        </ul>
    </nav>
    <section id="conteneur-formulaire">
        <form><p>Cliquez sur une question</p></form>
    </section>
    <section id="pop-up-creation" style="display: none;">
        <p>De quel type sera votre question?</p>
        <form id="formulaire-type-question">
            <select name="typeQuestion">
                <option value="">--Type de votre question--</option>
                <option value="Question à trou">Question à trou</option>
                <option value="Quizz">Quizz</option>
                <option value="Nouvelles associations">Nouvelles associations</option>
                <option value="Nouveau vrai ou faux">Nouveau vrai ou faux</option>
                <option value="Nouveau texte à trou">Nouveau texte à trou</option>
                <option value="Ouverte">Ouverte</option>
            </select>
            <button id="bouton-question-submit">Creer la question</button>
        </form>
    </section>
    <section id="pop-up-suppression" style="display: none;">
        <p>Voulez-vous supprimer cette question?</p>
        <button id="bouton-question-suppression">Supprimer la question</button>
    </section>
</div>


<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-3.3.1.min.js"><\/script>')</script>
<script src="<?php echo $_SESSION["URL_AUTRE"]; ?>/trumbowyg/dist/trumbowyg.min.js"></script>


<script type="text/javascript" src="<?php echo $_SESSION["URL_AUTRE"]; ?>/js/formulaires.js"></script>
<script type="text/javascript" src="<?php echo $_SESSION["URL_AUTRE"]; ?>/js/exerciseur.js"></script>
<script>
    init(<?php echo $exercice->id; ?>);
    
    chargerMenu();
</script>
