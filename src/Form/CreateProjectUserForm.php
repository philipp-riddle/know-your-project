<?php

namespace App\Form;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateProjectUserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (null !== $builder->getData()) {
            throw new \RuntimeException('This form is currently only for creating new ProjectUser entities');
        }

        $builder
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'id',
            ])
            ->add('userEmail', EmailType::class, [
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectUser::class,
            'csrf_protection' => false,
        ]);
    }
}
