<?php echo validation_errors(); ?>
<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/vraiOuFauxReponse/", array("class" => "formulaire")); ?>
    <?php $this->load->view('templates/ressource.php', array("groupe" => true)); ?>

    <?php foreach($propositions as $propositionBoucle){ ?>
        <label><?php echo $propositionBoucle->corps ?></label>
            <div class="choix">
                <input type="radio" checked="checked" value="1" name="vraiOuFaux[][<?php echo $propositionBoucle->id ?>]"/><label>Vrai</label>
                <input type="radio"   value="0" name="vraiOuFaux[][<?php echo $propositionBoucle->id ?>]"/><label>Faux</label>
            </div>
    <?php } ?>


    <div class="submit submit-exercice">
        <button type="submit">Valider</button>
    </div>
</form>