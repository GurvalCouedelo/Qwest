<?php echo validation_errors(); ?>
<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/ouverteReponse/", array("class" => "formulaire")); ?>
    <?php $this->load->view('templates/ressource.php', array("groupe" => false)); ?>

    <?php if($_SESSION["permission"] === "A"){ ?>
        <br/>
        <p>Vous n'avez pas à remplir cette question.</p>
        <div class="submit submit-exercice">
            <button type="submit">Envoyer</button>
        </div>
    <?php } ?>

<?php if($_SESSION["permission"] === "U"){ ?>
        <label>Votre réponse:</label>

        <input type="textarea" name="corps"/>
        <div class="submit submit-exercice">
            <button type="submit">Valider</button>
        </div>
    <?php } ?>
</form>