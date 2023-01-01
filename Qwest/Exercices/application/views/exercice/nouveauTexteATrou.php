<?php echo validation_errors(); ?>
<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/nouveauTexteATrouReponse/", array("class" => "formulaire")); 
        $tabQuestion = array();
?>
    <?php $this->load->view('templates/ressource.php', array("groupe" => false)); ?>
    <?php foreach($propositions as $propositionBoucle){ 
        $idSousQuestion = $propositionBoucle->idSousQuestion;
        if(!in_array($idSousQuestion, $tabQuestion)){
            
    ?>
        
        <?php if ($propositionBoucle->trouOuListe === "T"){ ?>
            <label><?php echo $propositionBoucle->corps ?></label>
            <input type="text" name="texteATrou[]"/>
        <?php } else if($propositionBoucle->trouOuListe === "L"){ ?>
            <label><?php echo $propositionBoucle->corps ?></label>
            <select name="texteATrou[]">
                <?php foreach($propositions as $propositionBoucle){ if($propositionBoucle->idSousQuestion === $idSousQuestion){?>
                    <option value="<?php echo $propositionBoucle->idPropo ?>"><?php echo $propositionBoucle->corpsPropo ?></option>
                <?php }} ?>
            </select>
        <?php } else if($propositionBoucle->trouOuListe === "F"){ ?>
            <label><?php echo $propositionBoucle->corps ?></label>
        <?php 
                }
            array_push($tabQuestion, $idSousQuestion);
            }
        }
    ?>


    <div class="submit submit-exercice">
        <button type="submit">Valider</button>
    </div>
</form>