<?php echo validation_errors(); ?>

<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/questionATrouReponse/", array(
    "id" => $token = "a" . substr(bin2hex(random_bytes(32)), 0, 16),
    "type_formulaire" => "propositionQuestionATrou",
    "id_entite" => $bonneReponse->id,
    "name" => "Proposition d'identifiant " . $bonneReponse->id
)); ?>
    <p class="en-tete-proposition">N° <?php echo $i; ?></p>
    <input type="text" name="corps" placeholder="Réponse proposée" value="<?php echo $bonneReponse->corps; ?>"/>
    <input type="text" name="commentaire" placeholder="Commentaire de la réponse" value="<?php echo $bonneReponse->commentaire; ?>"/>
    <span class="bouton-proposition-suppression" type-entite="reponse" entite-id="<?php echo $bonneReponse->id; ?>">✘</span>     
</form>