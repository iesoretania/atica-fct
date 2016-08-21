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

use AppBundle\Entity\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $aspects = [
            'form.grade_negative' => Report::GRADE_NEGATIVE,
            'form.grade_positive' => Report::GRADE_POSITIVE,
            'form.grade_excellent' => Report::GRADE_EXCELENT
        ];

        $builder
            ->add('workActivities', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => 'form.work_activities',
                'attr' => [
                    'rows' => 10
                ],
                'required' => true
            ])
            ->add('professionalCompetence', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => 'form.professional_competence',
                'expanded' => true,
                'choices_as_values' => true,
                'choices' => $aspects,
                'required' => true
            ])
            ->add('organizationalCompetence', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => 'form.organizational_competence',
                'expanded' => true,
                'choices_as_values' => true,
                'choices' => $aspects,
                'required' => true
            ])
            ->add('relationalCompetence', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => 'form.relational_competence',
                'expanded' => true,
                'choices_as_values' => true,
                'choices' => $aspects,
                'required' => true
            ])
            ->add('contingencyResponse', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => 'form.contingency_response',
                'expanded' => true,
                'choices_as_values' => true,
                'choices' => $aspects,
                'required' => true
            ])
            ->add('proposedChanges', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => 'form.proposed_changes',
                'attr' => [
                    'rows' => 10
                ],
                'required' => false
            ])
            ->add('signDate', 'Symfony\Component\Form\Extension\Core\Type\DateType', [
                'label' => 'form.sign_date',
                'widget' => 'single_text',
                'required' => true
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Report',
            'translation_domain' => 'student'
        ]);
    }
}
