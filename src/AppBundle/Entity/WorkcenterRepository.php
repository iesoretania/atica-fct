<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WorkcenterRepository extends EntityRepository
{
    public function getVisitRelatedWorkcenters(User $user)
    {
        $em = $this->getEntityManager();

        /**
         * ,
        (SELECT COUNT(a2) FROM AppBundle:Agreement a2 WHERE a2.workcenter = w AND a2.educationTutor = :user),
        (SELECT COUNT(v3), MIN(v3.date), MAX(v3.date) FROM AppBundle:Visit v3 WHERE v3.workcenter = w)
         */

        return $em->createQuery('SELECT DISTINCT w,
              (SELECT COUNT(a1) FROM AppBundle:Agreement a1 WHERE a1.workcenter = w AND a1.educationalTutor = :user),
              (SELECT COUNT(DISTINCT a2.student) FROM AppBundle:Agreement a2 WHERE a2.workcenter = w AND a2.educationalTutor = :user),
              (SELECT COUNT(v3) FROM AppBundle:Visit v3 WHERE v3.workcenter = w AND v3.teacher = :user),
              (SELECT MAX(v4.when) FROM AppBundle:Visit v4 WHERE v4.workcenter = w AND v4.teacher = :user),
              (SELECT MIN(v5.when) FROM AppBundle:Visit v5 WHERE v5.workcenter = w AND v5.teacher = :user)
              FROM AppBundle:Workcenter w JOIN AppBundle:Agreement a WITH a.workcenter = w JOIN AppBundle:Company c WHERE a.educationalTutor = :user ORDER BY c.name, w.name')
            ->setParameter('user', $user)
            ->getResult();
    }
}
