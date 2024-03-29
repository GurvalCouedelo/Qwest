<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ClassGroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClassGroupRepository extends EntityRepository
{
    public function FCBPOBC()
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select("cg")
            ->from($this->_entityName, "cg")
            ->leftJoin("cg.niveau", "n")
            ->orderBy("n.numeroOrdre")
        ;
        
        $query = $queryBuilder->getQuery();
        $resultat = $query->getResult();
        
        return $resultat;
    }
    
    public function FCBPANOBC($id)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select("cg")
            ->from($this->_entityName, "cg")
            ->leftJoin("cg.niveau", "n")
            ->where("n.id = :id")
                ->setParameter("id", $id)
            ->orderBy("n.numeroOrdre")
        ;
        
        $query = $queryBuilder->getQuery();
        $resultat = $query->getResult();
        
        return $resultat;
    }
}
