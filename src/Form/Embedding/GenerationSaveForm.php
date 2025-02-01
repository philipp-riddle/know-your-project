<?php

namespace App\Form\Embedding;

use App\Entity\Tag\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class GenerationSaveForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 3, 'max' => 255]),
            ],
            'required' => false,
        ]);

        $builder->add('content', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 3, 'max' => 4096]),
            ],
            'required' => false,
        ]);

        $builder->add('tag', EntityType::class, [
            'class' => Tag::class,
            'choice_label' => 'id',
            'required' => false,
        ]);

        $builder->add('checklistItems', CollectionType::class, [
            'entry_type' => TextType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
