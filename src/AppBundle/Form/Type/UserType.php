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
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('loginUsername', null, [
                'label' => 'form.login_username',
                'required' => false,
                'disabled' => !$options['admin']
            ])
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => 'form.email',
                'required' => false,
                'disabled' => !$options['admin']
            ])
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
            ]);

        if ($options['admin']) {
            $builder
                ->add('displayName', null, [
                    'label' => 'form.display_name',
                    'required' => false
                ]);
        }

        $builder
            ->add('gender', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => 'form.gender',
                'choices' => [
                    'form.gender_unknown' => Person::GENDER_UNKNOWN,
                    'form.gender_male' => Person::GENDER_MALE,
                    'form.gender_female' => Person::GENDER_FEMALE
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true
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
                ])
                ->add('financialManager', null, [
                    'label' => 'form.financial_manager',
                    'required' => false
                ])
                ->add('allowExternalLogin', ChoiceType::class, [
                    'label' => 'form.allow_external_login',
                    'required' => true,
                    'expanded' => true,
                    'choices' => [
                        'form.allow_external_login.no' => false,
                        'form.allow_external_login.yes' => true
                    ]
                ]);
        }


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $builder = $event->getForm();
            /** @var User $data */
            $data = $event->getData();

            if ($data->getAllowExternalLogin() || $options['admin']) {
                $builder
                    ->add('externalLogin', ChoiceType::class, [
                        'label' => 'form.external_login',
                        'required' => true,
                        'expanded' => true,
                        'choices' => [
                            'form.external_login.no' => false,
                            'form.external_login.yes' => true
                        ]
                    ]);
            }

            $builder
                ->add('tutorizedGroups', null, [
                    'label' => 'form.tutorized_groups',
                    'by_reference' => false,
                    'required' => false,
                    'disabled' => !$options['admin']
                ])
                ->add('studentGroup', null, [
                    'label' => 'form.student_group',
                    'placeholder' => 'form.student_group.placeholder',
                    'required' => false,
                    'disabled' => !$options['admin']
                ]);

            if (!$options['new']) {
                $builder
                    ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                        'label' => 'form.submit',
                        'attr' => ['class' => 'btn btn-success']
                    ]);

                if (!$options['admin'] && $options['me']) {
                    $builder
                        ->add('oldPassword', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
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
                    ->add('newPassword', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                        'label' => 'form.new_password',
                        'required' => $options['new'],
                        'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
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
                    ->add('changePassword', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                        'label' => 'form.change_password',
                        'attr' => ['class' => 'btn btn-success'],
                        'validation_groups' => ['Default', 'password']
                    ]);
            }
        });
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
