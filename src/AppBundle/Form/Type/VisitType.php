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

use AppBundle\Entity\User;
use AppBundle\Entity\Workcenter;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class VisitType extends AbstractType
{

    private $tokenStorage;
    private $managerRegistry;

    public function __construct(TokenStorageInterface $tokenStorage, ManagerRegistry $managerRegistry)
    {
        $this->tokenStorage = $tokenStorage;
        $this->managerRegistry = $managerRegistry;
    }

    public function addElements(FormInterface $form, Workcenter $workcenter = null, User $tutor = null)
    {
        $em = $this->managerRegistry->getManager();
        $students = $em->getRepository('AppBundle:User')->getStudentsByWorkcenterAndEducationalTutor($workcenter, $tutor);
        $workcenters = $em->getRepository('AppBundle:Workcenter')->getWorkcentersByEducationTutor($tutor);
        $form
            ->add('workcenter', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'label' => 'form.workcenter',
                'class' => 'AppBundle\Entity\Workcenter',
                'choices' => $workcenters,
                'placeholder' => 'form.select_workcenter',
                'required' => true
            ])
            ->add('notes', null, [
                'label' => 'form.notes',
                'required' => false
            ])
            ->add('students', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'label' => 'form.visited_students',
                'class' => 'AppBundle\Entity\User',
                'multiple' => true,
                'choices' => $students,
                'expanded' => true,
                'choice_label' => 'fullDisplayName',
                'required' => false
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tutor', null, [
                'label' => 'form.educational_tutor',
                'required' => true,
                'disabled' => true
            ])
            ->add('date', 'Symfony\Component\Form\Extension\Core\Type\DateType', [
                'label' => 'form.date',
                'widget' => 'single_text',
                'required' => true
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $this->addElements($form, $data->getWorkcenter(), $data->getTutor());
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            $form = $event->getForm();
            $data = $event->getData();

            /** @var Workcenter|null $workcenter */
            $workcenter = isset($data['workcenter']) ? $this->managerRegistry->getManager()->getRepository('AppBundle:Workcenter')->find($data['workcenter']) : null;

            $this->addElements($form, $workcenter, $form->get('tutor')->getData());
        });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Visit',
            'translation_domain' => 'visit'
        ]);
    }
}
