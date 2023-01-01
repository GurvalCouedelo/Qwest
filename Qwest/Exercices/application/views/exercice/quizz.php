<?php echo validation_errors(); ?>
<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/quizzReponse/", array("class" => "formulaire")); ?>
    <?php $this->load->view('templates/ressource.php', array("groupe" => false)); ?>
    <ul>
    <?php foreach($propositions as $propositionBoucle){ ?>
        <li>
            <div class="choix-complexe">
                <div class="cc-widget"><input type="checkbox" name="reponsesQuizz[]" value="<?php echo $propositionBoucle->id; ?>"/></div>
                <div class="cc-label"><label><?php echo $propositionBoucle->corps; ?></label></div>
            </div>
        </li>
    <?php } ?>
    </ul>
    <div class="submit submit-exercice">
        <button type="submit">Valider</button>
    </div>
</form>