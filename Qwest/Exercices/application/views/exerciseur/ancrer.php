<?php
    if($ancre !== "0"){
        ?>
        <script>chargerPage(<?php echo $ancre; ?>)</script>
        <?php
    }
    else{
        ?>
        <script>chargerIntro()</script>
        <?php
    }
?>