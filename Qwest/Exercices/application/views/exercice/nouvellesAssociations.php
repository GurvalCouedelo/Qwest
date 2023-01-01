<?php echo validation_errors(); ?>
<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/nouvellesAssociationsReponse/", array("class" => "formulaire")); ?>
    <?php $this->load->view('templates/ressource.php', array("groupe" => false)); ?>

    <?php foreach($questions as $questionBoucle){ ?>
        <div class="bloc-association">
            <div class="premiere-proposition-association">
                <?php echo $questionBoucle->proposition ?>
            </div>
                
            <select name="associations[]">
                <?php foreach($propositions as $propositionBoucle){ ?>
                    <option value="<?php echo $questionBoucle->sousQuestionId . "/" . $propositionBoucle->reponseId  ?>"><?php echo $propositionBoucle->corps ?></option>
                <?php } ?>
            </select>
        </div>
    <?php } ?>

    <div class="submit submit-exercice">
        <button type="submit">Valider</button>
    </div>
</form>