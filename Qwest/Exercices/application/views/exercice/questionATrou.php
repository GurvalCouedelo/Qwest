<?php echo validation_errors(); ?>
<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/questionATrouReponse/", array("class" => "formulaire")); ?>
    <?php $this->load->view('templates/ressource.php', array("groupe" => false)); ?>

    <input type="text" name="corps"/>
    <div class="submit submit-exercice">
        <button type="submit">Valider</button>
    </div>
</form>