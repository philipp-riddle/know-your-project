<?php

namespace App\Form\User;

use App\Entity\Project\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class UserMovementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'required' => true,
            ])
            ->add('routeName', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('mouseRelativeX', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 0, 'max' => 7000]),
                ],
            ])
            ->add('mouseRelativeY', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 0, 'max' => 7000]),
                ],
            ])
            ->add('hoveredElementDomPath', TextType::class, [
                'required' => false,
            ])
            ->add('hoveredElementOffsetRelativeX', NumberType::class, [
                'required' => false,
            ])
            ->add('hoveredElementOffsetRelativeY', NumberType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
