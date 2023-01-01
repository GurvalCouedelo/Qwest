<h1><?php echo $exercice->titre; ?></h1>
    <div class="conteneur">
        <p> Votre résultat est de: <span class="score"><?php echo round($correction["resultat"], 1); ?>/<?php echo $correction["totalPoints"]; ?></span> soit<span class="score"><?php echo round($correction["resultat"] / $correction["totalPoints"] * 100, 2); ?></span>%.</p><br/>
        
        <?php if($this->session->permission === "A"){ ?>
            <div class="div-adapt"> 
                <a class="bouton-lien-adapt neutre classique-neutre" href="<?php echo $_SESSION["LHS"] . "admin/exercices-liste/" ; echo $exercice->classeId; ?>">Retour à la liste des exercices</a><br/>
                <a class="bouton-lien-adapt neutre classique-neutre" href="<?php echo $_SESSION["LHS"] . "exercice/" . $this->session->exercice; ?>">Refaire l'exercice</a><br/>
                <a class="bouton-lien-adapt neutre classique-neutre" href="<?php echo $_SESSION["LHS"] . "admin/questions-liste/" . $this->session->exercice . "?true=false"; ?>">Modifier l'exercice</a>
            </div>
        <?php } else{ ?>
            <div class="div-adapt">
                <a class="bouton-lien-adapt neutre classique-neutre" href="<?php echo $_SESSION["LHS"] . "connecte"; ?>">Retourner sur le tableau de bord</a><br/>
                <a class="bouton-lien-adapt neutre classique-neutre" href="<?php echo $_SESSION["LHS"] . "connecte/exercices-liste/tous-les-exercices"; ?>">Retour à la liste des exercices</a><br/>
                <a class="bouton-lien-adapt neutre classique-neutre" href="<?php echo $_SESSION["LHS"] . "exercice/" . $this->session->exercice; ?>">Refaire l'exercice</a><br/>
            </div>
        <?php } ?>
        
        <?php if(count($points) === 1){ ?>
            <p>Votre résultat précédent pour cet exercice: <?php foreach($points as $pointBoucle){ ?> <?php echo $pointBoucle->points ?><?php } ?></p><br/>
        <?php } else if(count($points) > 1){ $i = 0; ?>
            <span class="texte">Vos résultats précédents pour cet exercice:</span>
                <?php foreach($points as $pointBoucle){ $i++;?>
                    <ul class="aligne">
                        <li><?php echo $pointBoucle->points ?> <?php if($i !== count($points)){ echo ""; } ?></li> 
                    </ul>
                <?php } ?>
            <br/>
        <?php } ?>
        
        <br/>
        
        <?php $i = 0; foreach($questions as $questionBoucle){ $i++; ?>
            <div class="reponse-resultat">
                <p><strong><?php echo $i . ". " . $questionBoucle->type . $questionBoucle->ennonce; ?></strong></p>
                
                
<!--                Questions à trou-->
                
                <?php if($questionBoucle->type === "Question à trou"){
                    $reponse = $serviceCorrection->obtenirReponses($questionBoucle->id)[0];
                    if($reponse->correction === "1"){
                        echo "<span style=\"color: green;\"><p>✔" . $reponse->corps . "</p></span>";
                    }
                            
                    else{
                        $bonnesReponses = $serviceCorrection->obtenirBonnesReponses($questionBoucle->id);
                        $nbBonnesReponses = count($bonnesReponses);
                        
                        if($nbBonnesReponses === 1){
                            echo "<span style=\"color: red;\"><p>✘" . $reponse->corps . "</p></span><p>La bonne réponse était: <br/><span style=\"color: green;\">"; 
                        }
                        else{
                            echo "<span style=\"color: red;\"><p>✘" . $reponse->corps . "</p></span><p>Les bonnes réponses étaient: <br/><span style=\"color: green;\">"; 
                        }
                        
                        foreach($bonnesReponses as $bonneReponseBoucle){
                            echo $bonneReponseBoucle->corps . "<br/>";
                        }
                        echo "</span></p>";
                    }    
                            
                } ?>
<!--                Quizz-->
                
                <?php if($questionBoucle->type === "Quizz"){
                    $reponses = $serviceCorrection->obtenirReponses($questionBoucle->id);
                    $propositions = $bonnesReponses = $serviceCorrection->obtenirBonnesReponses($questionBoucle->id);
    
                    echo "<p>";
                        
                    foreach($propositions as $propositionBoucle){
                        $faux = false;
                        $trouve = false;
                        
                        foreach($reponses as $reponseBoucle){
                            if($reponseBoucle->reponse_assoc_id === $propositionBoucle->id && $reponseBoucle->correction === "1"){
                                echo "☒ <span style=\"color: green;\">✔</span>";
                                $trouve = true;
                                break;
                            }
                            
                            else if($reponseBoucle->reponse_assoc_id === $propositionBoucle->id && $reponseBoucle->correction === "0"){
                                echo "<span style=\"color: red;\">☒ ✘";
                                $faux = true;
                                $trouve = true;
                                break;
                            }
                        }
                        
                        if($trouve === false){
                            if($propositionBoucle->verite === "0"){
                                echo "☐ <span style=\"color: green;\">✘</span>";
                            }
                            
                            else{
                                echo "<span style=\"color: rgb(234, 121, 8);\"> ☐  ✔";
                                $faux = true;
                            }
                        }
                        
                        echo $propositionBoucle->corps . "<br/>";
                        
                        if($faux === true){
                            echo "</span>";
                        }
                    }
                    
                    echo "</p>";
                            
                } ?>
        
        <?php if($questionBoucle->type === "Nouvelles associations"){
            $sousQuestions = $bonnesReponses = $serviceCorrection->obtenirSousQuestions($questionBoucle->id);
            $reponses = $serviceCorrection->obtenirReponsesAssociation($questionBoucle->id);
            $j = 0;
    
            foreach($sousQuestions as $sousQuestionsBoucle){
                echo "<p>";
                foreach($reponses as $reponseBoucle){
                    if($sousQuestionsBoucle->id === $reponseBoucle->sous_question_id){
                        echo $sousQuestionsBoucle->corps;
                    
                        if($reponseBoucle->correction === "1"){
                            echo "<span style=\"color: green;\">✔</span>" . $reponseBoucle->proposition . "</p>";
                        }
                        else{
                            echo "<span style=\"color: red;\">" . "✘" . $reponseBoucle->proposition ."</span></p>
                                <p>La bonne réponse était: <span style=\"color: green;\">" . $sousQuestionsBoucle->bonneReponse . "</span></p>";
                        }

                        if($sousQuestionsBoucle->commentaire !== null){
                            echo "<i>" . $sousQuestionsBoucle->commentaire . "</i><br/>";
                        }

                        echo "</p><br/>";
                        $j++;
                    }
                }
                
            }

            
        } ?>
                
        <?php if($questionBoucle->type === "Nouveau vrai ou faux"){
            $reponses = $serviceCorrection->obtenirReponsesVraiOuFaux($questionBoucle->id);
    
            $j = 0;
            foreach($reponses as $reponsesBoucle){
                echo "<p>";
                
                echo $reponsesBoucle->corps;
                
                if($reponsesBoucle->correction === "1"){
                    echo "<span style=\"color: green;\">";
                    
                    if($reponsesBoucle->bonneReponse === "1"){
                        echo "✔</span> Vrai</p>";
                    }
                    
                    else{
                        echo "✘</span> Faux</p>";
                    }
                }
                else{
                    
                    if($reponsesBoucle->bonneReponse === "1"){
                        echo "<span style=\"color: green;\">✔</span> <span style=\"color: red;\">✘ Faux</span></p>";
                    }
                    
                    else{
                        echo "<span style=\"color: green;\">✘</span> <span style=\"color: red;\">✔ Vrai</span></p>";
                    }
                }
                                    
                
                echo "</p><br/>";
                $j++;
            }

        } ?>
        <?php if($questionBoucle->type === "Nouveau texte à trou"){
            $reponses = $serviceCorrection->obtenirReponsesTexteATrou($questionBoucle->id);
            
            $j = 0;
            foreach($reponses as $reponsesBoucle){
                echo "<p>";
                
                echo $reponsesBoucle->corps . "<br/>";
                
                if($reponsesBoucle->correction === "1"){
                    if($reponsesBoucle->TOL === "T"){
                        echo "<span style=\"color: green;\">✔</span>" . $reponsesBoucle->reponse;
                    }
                    
                    else if($reponsesBoucle->TOL === "L"){
                        echo "<span style=\"color: green;\">✔</span>" . $reponsesBoucle->corpsProposition;
                    }
                    
                    
                }
                
                else{
                    if($reponsesBoucle->TOL === "T"){
                        echo "<span style=\"color: red;\">✘</span>" . $reponsesBoucle->reponse;
                    }
                    else if($reponsesBoucle->TOL === "L"){
                        echo "<span style=\"color: red;\">✘</span>" . $reponsesBoucle->corpsProposition;
                    }
                    
                    echo "<br/> Les bonnes réponses étaient: <br/>";
                    
                    $bonnesReponses = $serviceCorrection->obtenirBonnesReponsesTexteATrou($reponsesBoucle->sousQuestionId, $reponsesBoucle->TOL);
                    
                    foreach($bonnesReponses as $bonneReponseBoucle){
                        echo $bonneReponseBoucle->corps . "<br/>";
                    }
                    
                }
                    
                echo "</p><br/>";
                $j++;
            }

        } ?>

        </div>
    <?php } ?>