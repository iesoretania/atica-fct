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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
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
            ->add('username', null, [
                'label' => 'form.username',
                'required' => true
            ])
            ->add('reference', null, [
                'label' => 'form.reference',
                'property_path' => 'person.reference',
                'required' => false
            ])
            ->add('firstName', null, [
                'label' => 'form.first_name',
                'property_path' => 'person.firstName',
                'required' => true
            ])
            ->add('lastName', null, [
                'label' => 'form.last_name',
                'property_path' => 'person.lastName',
                'required' => true
            ])
            ->add('displayName', null, [
                'label' => 'form.display_name',
                'property_path' => 'person.displayName',
                'required' => true
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'form.gender',
                'property_path' => 'person.gender',
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
                'property_path' => 'person.initials',
                'required' => false
            ))
            ->add('email', EmailType::class, [
                'label' => 'form.email',
                'property_path' => 'person.email',
                'required' => false
            ]);

        if ($options['admin']) {
            $builder
                ->add('enabled', null, [
                    'label' => 'form.enabled',
                    'disabled' => $options['me'],
                    'required' => false
                ])
                ->add('globalAdministrator', null, [
                    'label' => 'form.global_administrator',
                    'required' => false,
                    'disabled' => $options['me']
                ]);
        }

        $this->additionalForm($builder, $options);

        if (!$options['new']) {
            $builder
                ->add('submit', SubmitType::class, [
                    'label' => 'form.submit',
                    'attr' => ['class' => 'btn btn-success']
                ]);

            if (!$options['admin'] && $options['me']) {
                $builder
                    ->add('oldPassword', PasswordType::class, [
                        'label' => 'form.old_password',
                        'required' => false,
                        'mapped' => false,
                        'constraints' => [
                            new UserPassword([
                                'groups' => ['password']
                            ]),
                            new NotBlank([
                                'groups' => ['password']
                            ])
                        ]
                    ]);
            }
        }

        if ($options['admin'] || $options['me']) {
            $builder
                ->add('newPassword', RepeatedType::class, [
                    'label' => 'form.new_password',
                    'required' => $options['new'],
                    'type' => PasswordType::class,
                    'mapped' => false,
                    'invalid_message' => 'password.no_match',
                    'first_options' => [
                        'label' => 'form.new_password',
                        'constraints' => [
                            new Length([
                                'min' => 7,
                                'minMessage' => 'password.min_length',
                                'groups' => ['password']
                            ]),
                            new NotBlank([
                                'groups' => ['password']
                            ])
                        ]
                    ],
                    'second_options' => [
                        'label' => 'form.new_password_repeat',
                        'required' => $options['new']
                    ]
                ])
                ->add('changePassword', SubmitType::class, [
                    'label' => 'form.change_password',
                    'attr' => ['class' => 'btn btn-success'],
                    'validation_groups' => ['Default', 'password']
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'translation_domain' => 'user',
            'admin' => false,
            'new' => false,
            'me' => false,
            'validation_groups' => ['Default']
        ]);
    }
}
