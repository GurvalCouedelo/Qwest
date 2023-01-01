<?php echo validation_errors(); ?>

<?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/exercice/questionATrouReponse/", array(
        "id" => "formulaireIntro"
    )); 
?>
    <div class="bande-actualisation" style="color: #e86d12;">Pas encore de modification <?php date_default_timezone_set('Europe/Paris'); echo date("H:i:s"); ?></div>
    <br/><br/>
    <textarea class="mes-editeurs" name="contenu" required="required"><?php echo $billet->contenu; ?></textarea><br/>

    <div class="ressources">
        <?php
            $sql = 'SELECT m.* FROM means m LEFT JOIN note ON m.id = note.ressource_id WHERE note.id = ?';
            $ressource = $this->db->query($sql, $billet->intro_id)->result();
//            var_dump($ressource);die;
            if(isset($ressource[0]))
            {
                $ressource = $ressource[0];
                echo "<div class=\"ressources\">";

                if($ressource->type === "I")
                {
                    ?>
                    <img src="<?php echo $_SESSION["LHS"] . 'uploads/img/' . $ressource->id . $ressource->extension . '.'. $ressource->extension; ?> ">
                    <?php
                }

                elseif($ressource->type === "V")
                {
                    ?>
                    <iframe width="375px" height="280px" src="<?php echo $ressource->lien ?>" frameborder="0" allowfullscreen></iframe>
                    <?php
                }

                echo "</div>";
            }
        ?>
    </div>
        
    <div class="div-adapt">
        <a class="bouton-lien-45 neutre classique-neutre" href="<?php echo $_SESSION["LHS"] ?>admin/question/attribution-ressource-initialisation/<?php echo $billet->intro_id; ?>/exercice">Modifier l'illustration</a>
        <a href="<?php echo $_SESSION["LHS"] ?>admin/banniere-suppression/<?php echo $billet->intro_id; ?>" class="bouton-suppression-45 suppr classique-suppr">Supprimer l'illustration</a>
    </div>
</form>