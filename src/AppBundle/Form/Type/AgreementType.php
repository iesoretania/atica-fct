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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgreementType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('student', null, [
                'label' => 'form.student',
                'required' => true
            ])
            ->add('workcenter', null, [
                'label' => 'form.workcenter',
                'required' => true
            ])
            ->add('workTutor', null, [
                'label' => 'form.work_tutor',
                'required' => true
            ])
            ->add('educationalTutor', null, [
                'label' => 'form.educational_tutor',
                'required' => true
            ])
            ->add('signDate', null, [
                'label' => 'form.sign_date',
                'required' => false
            ])
            ->add('fromDate', null, [
                'label' => 'form.from_date',
                'required' => true
            ])
            ->add('toDate', null, [
                'label' => 'form.to_date',
                'required' => true
            ])
            ->add('activities', null, [
                'label' => 'form.activities',
                'required' => false
            ]);
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
