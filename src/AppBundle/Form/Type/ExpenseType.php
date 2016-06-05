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

class ExpenseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', null, [
                'label' => 'form.date',
                'widget' => 'single_text',
                'required' => true
            ])
            ->add('route', null, [
                'label' => 'form.route',
                'required' => true
            ])
            ->add('distance', null, [
                'label' => 'form.mileage',
                'required' => true
            ])
            ->add('notes', null, [
                'label' => 'form.notes',
                'required' => false
            ])
            ->add('reviewed', null, [
                'label' => 'form.reviewed',
                'required' => false,
                'disabled' => !$options['admin']
            ])
            ->add('paid', null, [
                'label' => 'form.paid',
                'required' => false,
                'disabled' => !$options['admin']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Expense',
            'translation_domain' => 'expense',
            'admin' => false
        ]);
    }
}
