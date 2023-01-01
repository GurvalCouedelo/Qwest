<script src='https://cdn.tiny.cloud/1/l6wch4sqm21ilpc5nj95qint6s1be80zit853jaat1f3az8b/tinymce/5/tinymce.min.js' referrerpolicy="origin"></script>
<script>
    tinymce.init({
      selector: '#tiny'
    });
  </script>

<h1>Créer une question</h1>
<div class="conteneur">
    <?php echo validation_errors(); ?>
    <?php $this->load->helper('form'); echo form_open_multipart($_SESSION["URL"] . "/question/creer/" . $id . "/", array("class" => "formulaire")); ?>
        <div class="edition">
            <label>Texte de la question:</label>
            <textarea id="question_corps" name="corps" required="required"></textarea>

            <script type="text/javascript"> var CKEDITOR_BASEPATH = "<?php echo $_SESSION["LHSS"] ?>bundles/fosckeditor/";</script>
            <script type="text/javascript" src="<?php echo $_SESSION["LHSS"] ?>bundles/fosckeditor/ckeditor.js"></script>
            <script type="text/javascript">
                if (CKEDITOR.instances["question_corps"]) { CKEDITOR.instances["question_corps"].destroy(true); delete CKEDITOR.instances["question_corps"]; }
                CKEDITOR.replace("question_corps", {"language":"en"});
            </script>

            <label>Aide à la question:</label>
            <input name="aide" type="text"/>

            <div class="bloc-champ-edition">
                <label>Points attribués:</label>
                <input name="points" type="number" value="1"/>

                <label>Type de la question:</label>
                <select name="type[]">
                    <option value="Nouvelles associations">Nouvelles associations</option>
                    <option value="Nouveau vrai ou faux">Nouveau vrai ou faux</option>
                    <option value="Nouveau texte à trou">Nouveau texte à trou</option>
                </select>
            </div>

            <div class="submit submit-exercice">
                <button type="submit">Envoyer</button>
            </div>
        </div>
    </form>
</div>