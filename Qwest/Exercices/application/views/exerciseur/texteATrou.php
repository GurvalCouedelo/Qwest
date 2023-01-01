<script src='https://cdn.tiny.cloud/1/l6wch4sqm21ilpc5nj95qint6s1be80zit853jaat1f3az8b/tinymce/5/tinymce.min.js' referrerpolicy="origin"></script>
<script>
    tinymce.init({
      selector: '#tiny'
    });
</script>

<h1><strong class="important"><?php echo $question->type; ?>: </strong><?php echo (empty($donneesFormulaire)) ? "créer" : "modifier"; ?> un texte à trou</h1>
<div class="conteneur">
    <?php echo validation_errors(); ?>
    <?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/question/association/" . $id . "/" . (empty($donneesFormulaire)) ? "0" : $donneesFormulaire[0]->id , array("class" => "formulaire")); ?>
        <div class="edition">
            <?php echo $question->corps;?></br>
            <label>Corps du texte:</label>
            <textarea id="question_corps" name="corps" required="required"><?php echo (empty($donneesFormulaire)) ? "" : $donneesFormulaire[0]->texteATrou; ?></textarea>

            <script type="text/javascript"> var CKEDITOR_BASEPATH = "<?php echo $_SESSION["LHSS"] ?>bundles/fosckeditor/";</script>
            <script type="text/javascript" src="<?php echo $_SESSION["LHSS"] ?>bundles/fosckeditor/ckeditor.js"></script>
            <script type="text/javascript">
                if (CKEDITOR.instances["question_corps"]) { CKEDITOR.instances["question_corps"].destroy(true); delete CKEDITOR.instances["question_corps"]; }
                CKEDITOR.replace("question_corps", {"language":"en"});
            </script>
            


            <div class="submit submit-exercice">
                <button type="submit">Envoyer</button>
            </div>
        </div>
    </form>
</div>