<?php echo validation_errors(); ?>

<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/questionATrouReponse/", array(
    "id" => $token = "a" . substr(bin2hex(random_bytes(32)), 0, 16),
    "type_formulaire" => "texteATrou",
    "id_entite" => $question->idQ,
    "name" => "Texte à trou"
)); ?>
    
    <div class="bande-actualisation" style="color: #e86d12;">Pas encore de modification <?php date_default_timezone_set('Europe/Paris'); echo date("H:i:s"); ?></div>
    <label>Consigne générale:</label>
    <textarea id="my-editor" name="corps" required="required" placeholder="Consigne générale"><?php echo $question->corps; ?></textarea><br/>

    <input type="text" name="aide" placeholder="Aide à la question" value="<?php echo $question->aide; ?>"/>
    <input type="number" name="points" placeholder="Points attribués" value="<?php echo $question->points; ?>"/><br/><br/>

    <label>Contenu du texte à trou:</label>
    <textarea class="mes-editeurs" name="texteATrou" required="required"><?php echo $question->texteATrou; ?></textarea><br/>
</form>
