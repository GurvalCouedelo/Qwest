<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Entity\User;
use AppBundle\Entity\Points;

/**
 * ExerciseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ExerciseRepository extends EntityRepository
{
    public function FEBSAC($subject)
    {
        $session = new Session();
        
        $repository = $this->_em->getRepository(User::class);

        $utilisateur = $repository->findOneById($session->get("id"));
        $classe = $utilisateur->getClasseGroupe()->getNiveau();
        
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select("e")
            ->from($this->_entityName, "e")
            ->leftJoin("e.chapitre", "ch")
            ->leftJoin("ch.classe", "c")
            ->where("c.id = :classe")
                ->setParameter("classe", $classe->getId())
            ->leftJoin("ch.matiere", "m")
            ->andWhere("m.id = :matiere")
                ->setParameter("matiere", $subject)
        ;

        if($session->get("permission") === "U"){
            $queryBuilder
                ->andWhere("e.publie = :publie")
                ->setParameter("publie", true)
            ;
        }
        
        $query = $queryBuilder->getQuery();
        $resultat = $query->getResult();
        
        return $resultat;
    }
    
    public function FEBC($classroom)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select("e")
            ->from($this->_entityName, "e")
            ->leftJoin("e.chapitre", "ch")
            ->leftJoin("ch.classe", "c")
            ->leftJoin("ch.matiere", "m")
            ->where("c.id = :classroom")
                ->setParameter("classroom", $classroom)
            ->orderBy("m.ordre")
            ->addOrderBy("ch.id")
        ;
        
        $query = $queryBuilder->getQuery();
        $resultat = $query->getResult();
        
        return $resultat;
    }

    public function findExerciseOrderBy($publie = false)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select("e")
            ->from($this->_entityName, "e")
            ->leftJoin("e.chapitre", "ch")
            ->leftJoin("ch.classe", "c")
            ->leftJoin("ch.matiere", "m")
            ->leftJoin("e.difficulte", "d")
            ->orderBy("c.numeroOrdre")
            ->addOrderBy("m.ordre")
            ->addOrderBy("ch.id")
            ->addOrderBy("d.numeroOrdre")
        ;
        
        if($publie === true){
            $queryBuilder->where("e.publie = :true")
                ->setParameter(':true', true)
            ;
        }

        $query = $queryBuilder->getQuery();
        $resultat = $query->getResult();

        return $resultat;
    }
    
    public function findNumberOfExercises()
    {
        $QueryBuilder = $this->_em->createQueryBuilder()
            ->select("count(e.id)")
            ->from($this->_entityName, "e")
        ;

        $query = $QueryBuilder->getQuery();
        return $query->getSingleScalarResult();
    }
    
    public function findExercicesMoins6Heures()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select("e")
            ->from($this->_entityName, "e")
            ->where('e.derniereActualisation IS NOT NULL')
            ->andWhere('e.derniereActualisation < :dateLimite')
                ->setParameter(':dateLimite', new \DateTime('-6 hour'))
        ;

        return $qb->getQuery()->getResult();
    }
    
    public function findNumberOfExercicesNonActualise()
    {
        $QueryBuilder = $this->_em->createQueryBuilder()
            ->select("count(e.id)")
            ->from($this->_entityName, "e")
            ->where('e.derniereActualisation IS NULL')
        ;

        $query = $QueryBuilder->getQuery();
        return $query->getSingleScalarResult();
    }
    
    public function actualisationScoresExercices(){
        $repositoryPoints = $this->getEntityManager()->getRepository(Points::class);
        $em = $this->getEntityManager();

        $qb = $this->_em->createQueryBuilder()
            ->select("e")
            ->from($this->_entityName, "e")
        ;

        $query = $qb->getQuery()->getResult();

        foreach($query as $exercice)
        {
            $tableauScore = $repositoryPoints->countScoreByExercice($exercice->getId());
            
            $q = $this->createQueryBuilder("e")
                ->update("AppBundle\Entity\Exercise", 'e')
                ->set('e.totalScores', '?1')
                ->set('e.moyenneScores', '?2')
                ->set('e.medianeScores', '?3')
                ->set('e.scores100', '?4')
                ->set('e.nbScores', '?5')
                ->set('e.derniereActualisation', '?6')
                ->where('e.id = ?7')
                ->setParameter(1, $tableauScore["total"])
                ->setParameter(2, $tableauScore["moyenne"])
                ->setParameter(3, $tableauScore["mediane"])
                ->setParameter(4, $tableauScore["nbScores100"])
                ->setParameter(5, $tableauScore["nbScores"])
                ->setParameter(6, new \Datetime())
                ->setParameter(7,  $exercice->getId())
                ->getQuery();
            $q->execute();

            $em->persist($exercice);
        }

        $em->flush();

    }
    
    public function verifActualisationExercice()
    {

        $exercicesMoins6Heures = $this->findExercicesMoins6Heures();
        $i = 0;

        foreach($exercicesMoins6Heures as $exerciceBoucle){
            $i++;
        }

        if($i === 0){
            $nombreExercices = $this->findNumberOfExercises();
            $nombreExercicesNonActualise = $this->findNumberOfExercicesNonActualise();
                
            if($nombreExercices === $nombreExercicesNonActualise){
                $this->actualisationScoresExercices();
            }
        }
        
        if($i !== 0){
            $this->actualisationScoresExercices();
        }
        
        $this->actualisationScoresExercices();
    }
    
    public function findExercicesPrioritaires($utilisateur){
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select("e")
            ->from($this->_entityName, "e")
            ->leftJoin("e.prioritaire", "p")
            ->where("p.id = :niveauId")
                ->setParameter("niveauId", $utilisateur->getclasseGroupe()->getNiveau()->getId())
            ->orderBy("e.id", "DESC")
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
