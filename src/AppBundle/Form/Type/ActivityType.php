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

use AppBundle\Entity\Activity;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('learningOutcome', null, [
                'label' => 'form.learning_outcome',
                'required' => true,
                'disabled' => $options['fixed']
            ])
            ->add('code', null, [
                'label' => 'form.code',
                'required' => false
            ])
            ->add('name', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => 'form.name',
                'required' => true
            ])
            ->add('description', null, [
                'label' => 'form.description',
                'required' => false
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            /** @var Activity $activity */
            $activity = $event->getData();

            $event->getForm()
                ->add('criteria', null, [
                    'label' => 'form.criteria.assoc',
                    'expanded' => true,
                    'query_builder' => function(EntityRepository $er) use ($activity) {
                        return $er
                            ->createQueryBuilder('c')
                            ->where('c.learningOutcome = :lo')
                            ->setParameter('lo', $activity->getLearningOutcome())
                            ->orderBy('c.orderNr')
                            ->addOrderBy('c.code')
                            ->addOrderBy('c.name')
                            ;
                    },
                    'required' => false
                ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Activity',
            'translation_domain' => 'activity',
            'fixed' => false
        ]);
    }
}
