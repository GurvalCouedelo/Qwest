<?php echo validation_errors(); ?>
<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/texteATrouReponse/", array("class" => "formulaire")); 
        $tabQuestion = array();
?>
    <?php $this->load->view('templates/ressource.php', array("groupe" => true)); ?>
    <?php foreach($propositions as $propositionBoucle){ 
        $idQuestion = $propositionBoucle->idQuestion;
        if(!in_array($idQuestion, $tabQuestion)){
            
    ?>
        <label><?php echo $propositionBoucle->corps ?></label>
        <?php if ($propositionBoucle->trouOuListe === "T"){ ?>
            <input type="text" name="texteATrou[]"/>
        <?php } else{ ?>
            <select name="texteATrou[]">
                    <?php foreach($propositions as $propositionBoucle){ if($propositionBoucle->idQuestion === $idQuestion){?>
                        
                        <option value="<?php echo $propositionBoucle->idPropo ?>/<?php echo $idQuestion ?>"><?php echo $propositionBoucle->corpsPropo ?></option>


                    <?php }} ?>
                </select>
        <?php } ?>
    <?php 
                array_push($tabQuestion, $idQuestion);
            }
        }
    ?>


    <div class="submit submit-exercice">
        <button type="submit">Valider</button>
    </div>
</form>