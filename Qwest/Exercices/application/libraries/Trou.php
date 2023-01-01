<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Trou{
    public function transformer($texte, $question)
    {
        $CI = &get_instance();
        
        if(!preg_match("#[{}[\]]#", $texte))
        {
            return "Votre texte ne contient pas de choix.";
        }
        
        $texteCrochet[0] = preg_split("#\[#", $texte);
        $texteCrochet[1] = preg_split("#\]#", $texte);
        $texteCrochet[2] = preg_split("#\{#", $texte);
        $texteCrochet[3] = preg_split("#\}#", $texte);
        
        if(count($texteCrochet[0]) !== count($texteCrochet[1]) || count($texteCrochet[2]) !== count($texteCrochet[3])){
            return "Votre texte est mal construit, il ne contient pas le même nombre de balise ouvrante que de balise fermante.";
        }
        
        if(preg_match("#[[{]([^}\]])*[[{]#s", $texte)){
            return "Votre texte est mal construit, veyez à ne pas imbriquer les balises ouvrantes comme tel: [[ ]].";
        }
            
//        Démarrage du travail sur le texte
        
        $texte = preg_replace("#(<p>|</p>)#","", $texte);
        
        $sql = "UPDATE question SET texteATrou = ? WHERE id = ?";
        $CI->db->query($sql, array(
            $texte,
            $question
        ));
        
        $tableauPartition = preg_split("#[}\]]#", $texte);
        
        $longueuerTableau = count($tableauPartition);
        
        for($i = 0; $i < $longueuerTableau; $i++)
        {
//        Modification à faire
            
            $tableauDeuxParties = preg_split("#(\[|\{)#", $tableauPartition[$i]);
            
            if(count(preg_split("#\[#", $tableauPartition[$i])) > count(preg_split("#\{#", $tableauPartition[$i])))
            {
                $typeChamp = "T";
            }
            
            elseif(count(preg_split("#\[#", $tableauPartition[$i])) < count(preg_split("#\{#", $tableauPartition[$i])))
            {
                $typeChamp = "L";
            }
            
            elseif(count(preg_split("#\[#", $tableauPartition[$i])) === count(preg_split("#\{#", $tableauPartition[$i])))
            {
                $typeChamp =  "F";
            }
            
            
            $sql = "INSERT INTO sub_question (question_id, corps, trouOuListe) VALUES (?, ?, ?)";
                $CI->db->query($sql, array(
                    $question,
                    $tableauDeuxParties[0],
                    $typeChamp
                ));
                $sousQuestionId = $CI->db->insert_id();
            
            if(isset($tableauDeuxParties[1]))
            {
                $tableauProposition = preg_split("#\*#", $tableauDeuxParties[1]);
                $longueuerTableauProposition = count($tableauProposition);
                

                for($j = 0; $j < $longueuerTableauProposition; $j++)
                {
                    
                    if($j === 0){
                        $verite = true;
                    }
                    else{
                        $verite = false;
                    }
                    
                    $sql = "INSERT INTO good_answer (sous_question_id, corps, verite) VALUES (?, ?, ?)";
                    $CI->db->query($sql, array(
                        $sousQuestionId,
                        $tableauProposition[$j],
                        $verite
                    ));
                }
            }
        }
        
        return true;
    }
}