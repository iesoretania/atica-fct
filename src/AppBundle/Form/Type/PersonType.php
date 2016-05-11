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

use AppBundle\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    public function additionalForm(FormBuilderInterface $builder, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', null, [
                'label' => 'form.reference',
                'required' => false
            ])
            ->add('firstName', null, [
                'label' => 'form.first_name',
                'required' => true
            ])
            ->add('lastName', null, [
                'label' => 'form.last_name',
                'required' => true
            ])
            ->add('displayName', null, [
                'label' => 'form.display_name',
                'required' => false
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'form.gender',
                'choices_as_values' => true,
                'choices' => [
                    'form.gender_unknown' => Person::GENDER_UNKNOWN,
                    'form.gender_male' => Person::GENDER_MALE,
                    'form.gender_female' => Person::GENDER_FEMALE
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true
            ])
            ->add('initials', null, array(
                'label' => 'form.initials',
                'required' => false
            ))
            ->add('email', EmailType::class, [
                'label' => 'form.email',
                'required' => false
            ])
            ->add('phoneNumber', null, [
                'label' => 'form.email',
                'required' => false
            ])
            ->add('address', null, [
                'label' => 'form.address',
                'required' => false
            ])
            ->add('city', null, [
                'label' => 'form.city',
                'required' => false
            ])
            ->add('province', null, [
                'label' => 'form.province',
                'required' => false
            ])
            ->add('zipCode', null, [
                'label' => 'form.zip_code',
                'required' => false
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Person',
            'translation_domain' => 'person',
        ]);
    }
}
