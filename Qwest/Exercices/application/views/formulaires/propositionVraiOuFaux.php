<?php echo validation_errors(); ?>

<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/questionATrouReponse/", array(
    "id" => $token = "a" . substr(bin2hex(random_bytes(32)), 0, 16),
    "type_formulaire" => "propositionVraiOuFaux",
    "id_entite" => $premiereProposition->id,
    "name" => "Proposition d'identifiant " . $premiereProposition->id
)); ?>

    <p class="en-tete-proposition"> N° <?php echo $i; ?></p>
    <input type="radio" <?php if($secondeProposition->verite === "1"){ echo "checked=\"checked\""; } ?>  value="1" name="radioVraiFaux"/><label>Vrai</label>
    <input type="radio" <?php if($secondeProposition->verite === "0"){ echo "checked=\"checked\""; } ?> value="0" name="radioVraiFaux"/><label>Faux</label>
    <br/><br/>
    <textarea class="mes-editeurs" name="premiereProposition" required="required"><?php echo $premiereProposition->corps; ?></textarea><br/>
    <span class="bouton-proposition-suppression" type-entite="sousQuestion" entite-id="<?php echo $premiereProposition->id; ?>">✘</span>   
    
    
</form>