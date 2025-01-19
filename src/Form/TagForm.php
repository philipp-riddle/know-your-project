<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // if the tag does not exist yet the user must specify the project the tag belongs to
        if (null === $builder->getData()) {
            $builder->add('project', EntityType::class, [
                'required' => true,
                'class' => Project::class,
                'choice_label' => 'id',
            ]);
        }

        $builder->add('name', options: [
            'required' => true,
        ]);

        $builder->add('color', options: [
            'required' => true,
        ]);

        // this allows the user to specify a new or a new parent tag
        $builder->add('parent', EntityType::class, [
            'required' => true,
            'mapped' => false,
            'class' => Tag::class,
            'choice_label' => 'id',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
            'csrf_protection' => false,
        ]);
    }
}
