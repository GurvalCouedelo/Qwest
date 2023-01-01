<?php
    if($groupe === false){
        $sql = 'SELECT * FROM means m LEFT JOIN means_question mq ON m.id = mq.means_id WHERE mq.question_id = ?';
        $ressources = $this->db->query($sql, $this->session->question->id)->result();
    }

    else{
        $sql = 'SELECT * FROM means m LEFT JOIN means_association_group ma ON m.id = ma.means_id WHERE ma.association_group_id = ?';
        $ressources = $this->db->query($sql, $this->session->groupe["id"])->result();
    }

    if(count($ressources) !== 0)
    {
        ?>
            <div class="ressources">
        <?php
        foreach($ressources as $ressource)
        {
                    
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
        }

        ?>
            </div>
        <?php
    }