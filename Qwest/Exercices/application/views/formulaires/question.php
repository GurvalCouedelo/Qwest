<?php echo validation_errors(); ?>

<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/questionATrouReponse/", array(
    "id" => $token = "a" . substr(bin2hex(random_bytes(32)), 0, 16),
    "type_formulaire" => "questionBasique",
    "id_entite" => $question->questionId,
    "name" => $question->type . " n°" . $question->numeroOrdre
)); ?>
    
    <div class="bande-actualisation" style="color: #e86d12;">Pas encore de modification <?php date_default_timezone_set('Europe/Paris'); echo date("H:i:s"); ?></div>
    <label>Texte de la question:</label>
    <textarea id="my-editor" name="corps" required="required"><?php echo $question->corps; ?></textarea><br/>

    <input type="text" name="aide" placeholder="Aide à la question" value="<?php echo $question->aide; ?>"/>
    <input type="number" name="points" placeholder="Points attribués" value="<?php echo $question->points; ?>"/>
    <br/><br/>

    <?php 
        if(count($ressources) != 0){
            echo "<p>Ressources:</p>";
        } 
        else{
            echo "<p>Il n'y a pas encore de ressources.</p>";
        }
    ?>
    
    <ul class="liste-ressources">
        <?php 
            foreach($ressources as $ressourceBoucle){
                ?>
                <li> <?php echo $ressourceBoucle->alt ?> <span class="bouton-ressources-suppression" question-id="<?php echo $question->questionId; ?>" ressource-id="<?php echo $ressourceBoucle->mId; ?>">✘</span> </li> 
                <?php
            }
        ?>
    </ul>
    <button class="bouton-creation"><a href="<?php echo $_SESSION["LHS"] ?>admin/question/attribution-ressource-initialisation/<?php echo $question->questionId; ?>/question">Ajouter une ressource +</a></button>
</form>

