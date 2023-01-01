<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AssociationGroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AssociationGroupRepository extends EntityRepository
{
    public function selectAssociationGroup()
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select("g")
            ->from($this->_entityName,'g')
            ->leftJoin("g.question", "q")
        ;

        $groupes = $queryBuilder->getQuery()->getResult();
        
        return $groupes;
    }
}