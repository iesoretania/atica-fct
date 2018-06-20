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

class AttendanceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startTime1', 'Symfony\Component\Form\Extension\Core\Type\TimeType', [
                'label' => 'form.start_time1',
                'input' => 'string',
                'widget' => 'single_text',
                'with_seconds' => false,
                'required' => true
            ])
            ->add('endTime1', 'Symfony\Component\Form\Extension\Core\Type\TimeType', [
                'label' => 'form.end_time1',
                'input' => 'string',
                'widget' => 'single_text',
                'with_seconds' => false,
                'required' => true
            ])
            ->add('startTime2', 'Symfony\Component\Form\Extension\Core\Type\TimeType', [
                'label' => 'form.start_time2',
                'input' => 'string',
                'widget' => 'single_text',
                'with_seconds' => false,
                'required' => false
            ])
            ->add('endTime2', 'Symfony\Component\Form\Extension\Core\Type\TimeType', [
                'label' => 'form.end_time2',
                'input' => 'string',
                'widget' => 'single_text',
                'with_seconds' => false,
                'required' => false
            ])
            ->add('startTime3', 'Symfony\Component\Form\Extension\Core\Type\TimeType', [
                'label' => 'form.start_time3',
                'input' => 'string',
                'widget' => 'single_text',
                'with_seconds' => false,
                'required' => false
            ])
            ->add('endTime3', 'Symfony\Component\Form\Extension\Core\Type\TimeType', [
                'label' => 'form.end_time3',
                'input' => 'string',
                'widget' => 'single_text',
                'with_seconds' => false,
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
            'data_class' => 'AppBundle\Form\Model\Attendance',
            'translation_domain' => 'attendance_report'
        ]);
    }
}
