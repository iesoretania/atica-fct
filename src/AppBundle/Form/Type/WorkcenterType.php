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

class WorkcenterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'form.name',
                'required' => true
            ])
            ->add('company', null, [
                'label' => 'form.company',
                'disabled' => $options['locked'],
                'required' => true
            ])
            ->add('address', null, [
                'label' => 'form.address',
                'required' => true
            ])
            ->add('city', null, [
                'label' => 'form.city',
                'required' => true
            ])
            ->add('province', null, [
                'label' => 'form.province',
                'required' => true
            ])
            ->add('zipCode', null, [
                'label' => 'form.zip_code',
                'required' => true
            ])
            ->add('phoneNumber', null, [
                'label' => 'form.phone_number',
                'required' => false
            ])
            ->add('email', null, [
                'label' => 'form.email',
                'required' => false
            ])
            ->add('manager', null, [
                'label' => 'form.manager',
                'required' => true
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Workcenter',
            'locked' => false,
            'translation_domain' => 'workcenter'
        ]);
    }
}
