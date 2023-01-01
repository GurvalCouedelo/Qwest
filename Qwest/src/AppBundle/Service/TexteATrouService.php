<?php

namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\Answer;
use AppBundle\Entity\Question;
use AppBundle\Entity\GoodAnswer;
use AppBundle\Entity\AssociationGroup;
use AppBundle\Entity\TypeQuestion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;

class TexteATrouService extends Controller
{
    public function texteAEntite($texte, $groupe)
    {
        $questionService = $this->container->get(QuestionService::class);
        $repositoryTypeQuestion = $this->getDoctrine()->getManager()->getRepository(TypeQuestion::class);
        $repositoryQuestion = $this->getDoctrine()->getManager()->getRepository(Question::class);
        $em = $this->getDoctrine()->getManager();
        
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
        
//        Modification à faire
        $tableauPartition = preg_split("#[}\]]#", $texte);
        
        $typeQuestion = $repositoryTypeQuestion->findOneByNom("Texte à trou");
        $questionRecherche = $repositoryQuestion->findQuestionOrderByNumeroOrdre($groupe->getExercice());
        
        
        foreach($groupe->getQuestion() as $question){
            $groupe->removeQuestion($question);
            $em->remove($question);
        }
        
        $em->flush();
        
        $questionService->verifNumeroOrdre($groupe->getExercice());
        $i = 0;
        
        $questionRecherche = $repositoryQuestion->findQuestionOrderByNumeroOrdre($groupe->getExercice());
        foreach($questionRecherche as $questionTemp)
        {
            $i++;
        }
        if($i === 0)
        {
            $numeroOrdre = 1;
        }
        else
        {
            $IdDernQuest = 0;
            foreach($questionRecherche as $questionTemp)
            {
                $IdDernQuest = $questionTemp->getNumeroOrdre();
            }
            
            $numeroOrdre = $IdDernQuest + 1;
        }
                        
        $em->flush();

        $longueuerTableau = count($tableauPartition);
        
        for($i = 0; $i < $longueuerTableau; $i++)
        {
//        Modification à faire
            $questionTableau[$i] = new Question();
            
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
                $typeChamp = null;
                $groupe->setFinTexte($tableauPartition[$i]);
                break;
            }
            
            $questionTableau[$i]->setCorps($tableauDeuxParties[0]);
            $questionTableau[$i]->setGroupe($groupe);
            $questionTableau[$i]->setExercice($groupe->getExercice());
            $questionTableau[$i]->setType($typeQuestion);
            $questionTableau[$i]->setTrouOuListe($typeChamp);
            $questionTableau[$i]->setNumeroOrdre($numeroOrdre);
            
            if(isset($tableauDeuxParties[1]))
            {
                $tableauProposition = preg_split("#\*#", $tableauDeuxParties[1]);

                $longueuerTableauProposition = count($tableauProposition);

                for($j = 0; $j < $longueuerTableauProposition; $j++)
                {
                    
                    $reponseTableau[$j] = new GoodAnswer();
                    $reponseTableau[$j]->setQuestion($questionTableau[$i]);
                    if($j === 0){
                        $reponseTableau[$j]->setVerite(true);
                    }
                    else{
                        $reponseTableau[$j]->setVerite(false);
                    }
                    
                    $reponseTableau[$j]->setCorps($tableauProposition[$j]);
                    $questionTableau[$i]->addBonneReponse($reponseTableau[$j]);
                }
            }

            $numeroOrdre++;
            $em->persist($questionTableau[$i]);
        }
        
        
        $groupe->setTexteATrou($texte);
        $em->flush();
        
        return true;
    }
}