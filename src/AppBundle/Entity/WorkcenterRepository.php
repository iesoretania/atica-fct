<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WorkcenterRepository extends EntityRepository
{
    public function getVisitRelatedWorkcenters(User $user)
    {
        $em = $this->getEntityManager();

        return $em->createQuery('SELECT DISTINCT w,
              (SELECT COUNT(a1) FROM AppBundle:Agreement a1 WHERE a1.workcenter = w AND a1.educationalTutor = :user),
              (SELECT COUNT(DISTINCT a2.student) FROM AppBundle:Agreement a2 WHERE a2.workcenter = w AND a2.educationalTutor = :user),
              (SELECT COUNT(v3) FROM AppBundle:Visit v3 WHERE v3.workcenter = w AND v3.tutor = :user),
              (SELECT MAX(v4.date) FROM AppBundle:Visit v4 WHERE v4.workcenter = w AND v4.tutor = :user),
              (SELECT MIN(v5.date) FROM AppBundle:Visit v5 WHERE v5.workcenter = w AND v5.tutor = :user)
              FROM AppBundle:Workcenter w JOIN AppBundle:Agreement a WITH a.workcenter = w JOIN AppBundle:Company c WHERE a.educationalTutor = :user ORDER BY w.name')
            ->setParameter('user', $user)
            ->getResult();
    }

    public function getWorkcentersByEducationTutor(User $user)
    {
        $em = $this->getEntityManager();

        return $em->createQuery('SELECT DISTINCT w
              FROM AppBundle:Workcenter w JOIN AppBundle:Agreement a WITH a.workcenter = w JOIN AppBundle:Company c WHERE a.educationalTutor = :user ORDER BY w.name')
            ->setParameter('user', $user)
            ->getResult();
    }
}
