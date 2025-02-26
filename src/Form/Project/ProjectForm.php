<?php

namespace App\Form\Project;

use App\Entity\Project\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name');

        // users can select a project right after creating it when sending this parameter
        $builder->add('selectAfterCreating', null, [
            'required' => false,
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'csrf_protection' => false,
        ]);
    }
}
