<?php
/*
  ÁTICA - Aplicación web para la gestión documental de centros educativos

  Copyright (C) 2015-2016: Luis Ramón López López

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see [http://www.gnu.org/licenses/].
*/

namespace AppBundle\Form\Type;

use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AgreementType extends AbstractType
{
    private $tokenStorage;
    private $managerRegistry;

    public function __construct(TokenStorageInterface $tokenStorage, ManagerRegistry $managerRegistry)
    {
        $this->tokenStorage = $tokenStorage;
        $this->managerRegistry = $managerRegistry;
    }

    public function addElements(FormInterface $form, User $student = null, Company $company = null)
    {
        $activities = null === $student ? [] : $student->getStudentGroup()->getTraining()->getActivities();
        $workcenters = null === $company ? [] : $company->getWorkcenters();

        $form
            ->add('company', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'label' => 'form.company',
                'class' => 'AppBundle\Entity\Company',
                'mapped' => false,
                'data' => $company,
                'placeholder' => 'form.select_company',
                'required' => true
            ])
            ->add('workcenter', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'label' => 'form.workcenter',
                'class' => 'AppBundle\Entity\Workcenter',
                'choice_label' => 'name',
                'choices' => $workcenters,
                'required' => true
            ])
            ->add('workTutor', null, [
                'label' => 'form.work_tutor',
                'choice_label' => 'fullPersonDisplayName',
                'required' => true,
                'attr' => ['class' => 'person']
            ])
            ->add('educationalTutor', null, [
                'label' => 'form.educational_tutor',
                'choice_label' => 'fullPersonDisplayName',
                'required' => true,
                'attr' => ['class' => 'person']
            ])
            ->add('signDate', null, [
                'label' => 'form.sign_date',
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('fromDate', null, [
                'label' => 'form.from_date',
                'widget' => 'single_text',
                'required' => true
            ])
            ->add('toDate', null, [
                'label' => 'form.to_date',
                'widget' => 'single_text',
                'required' => true
            ])
            ->add('activities', null, [
                'label' => 'form.activities',
                'expanded' => true,
                'required' => false,
                'choices' => $activities
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var User $user
         */
        $user = $this->tokenStorage->getToken()->getUser();

        $builder
            ->add('student', null, [
                'label' => 'form.student',
                'placeholder' => 'form.select_student',
                'choice_label' => 'fullDisplayName',
                'query_builder' => function(EntityRepository $er) use ($user) {
                    $qb = $er->createQueryBuilder('u')
                        ->orderBy('u.lastName', 'ASC')
                        ->addOrderBy('u.firstName', 'ASC')
                        ->innerJoin('u.studentGroup', 'g');

                    if (!$user->isGlobalAdministrator()) {
                        $qb = $qb
                            ->innerJoin('g.training', 't')
                            ->innerJoin('t.department', 'd')
                            ->where('g.id IN (:groups)')
                            ->orWhere('d.head = :user')
                            ->setParameter('groups', $user->getTutorizedGroups()->toArray())
                            ->setParameter('user', $user);
                    }
                    return $qb;
                },
                'required' => true
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $company = $data->getWorkcenter() ? $data->getWorkcenter()->getCompany() : null;

            $this->addElements($form, $data->getStudent(), $company);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {

            $form = $event->getForm();
            $data = $event->getData();

            /** @var Company|null $company */
            $company = isset($data['company']) ? $this->managerRegistry->getManager()->getRepository('AppBundle:Company')->find($data['company']) : null;

            /** @var User|null $student */
            $student = $this->managerRegistry->getManager()->getRepository('AppBundle:User')->find($data['student']);

            $this->addElements($form, $student, $company);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Agreement',
            'translation_domain' => 'agreement'
        ]);
    }
}
