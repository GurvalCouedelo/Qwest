<?php echo validation_errors(); ?>
<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/associationReponse/", array("class" => "formulaire")); ?>
    <?php $this->load->view('templates/ressource.php', array("groupe" => true)); ?>

    <?php foreach($propositions as $propositionBoucle){ ?>
        <div class="bloc-association">
            <div class="premiere-proposition-association">
                <?php echo $propositionBoucle->corps ?>
            </div>
                
            <select name="associations[]">
                <?php foreach($propositions as $propositionBoucle){ ?>
                    <option value="<?php echo $propositionBoucle->idPropo ?>"><?php echo $propositionBoucle->corpsPropo ?></option>
                <?php } ?>
            </select>
        </div>
    <?php } ?>


    
    <div class="submit submit-exercice">
        <button type="submit">Valider</button>
    </div>
</form>