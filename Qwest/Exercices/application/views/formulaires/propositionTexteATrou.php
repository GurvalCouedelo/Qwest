<?php echo validation_errors(); ?>

<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/questionATrouReponse/", array(
    "id" => $token = "a" . substr(bin2hex(random_bytes(32)), 0, 16),
    "type_formulaire" => "propositionTexteATrou",
    "id_entite" => $question->idQ,
    "name" => "Texte à trou d'identifiant " . $question->idQ
)); ?>
    <label>Contenu du texte à trou:</label>
    <textarea class="mes-editeurs" name="texteATrou" required="required"><?php echo $question->texteATrou; ?></textarea><br/>
    
</form>