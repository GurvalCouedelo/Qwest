<?php echo validation_errors(); ?>

<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/questionATrouReponse/", array(
    "id" => $token = "a" . substr(bin2hex(random_bytes(32)), 0, 16),
    "type_formulaire" => "propositionAssociation",
    "id_entite" => $premiereProposition->id,
    "name" => "Proposition d'identifiant " . $premiereProposition->id
)); ?>

    <p class="en-tete-proposition"> N° <?php echo $i; ?></p><br/><br/>
    <textarea class="mes-editeurs" name="premiereProposition" required="required"><?php echo $premiereProposition->corps; ?></textarea><br/>
    <input type="text" name="secondeProposition" placeholder="Contenu de la seconde proposition" value="<?php echo $secondeProposition->corps; ?>"/>
    <span class="bouton-proposition-suppression" type-entite="sousQuestion" entite-id="<?php echo $premiereProposition->id; ?>">✘</span>   
</form>