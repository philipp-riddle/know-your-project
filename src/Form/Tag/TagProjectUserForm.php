<?php

namespace App\Form\Tag;

use App\Entity\Page;
use App\Entity\Project\ProjectUser;
use App\Entity\Tag\Tag;
use App\Entity\Tag\TagPage;
use App\Entity\Tag\TagProjectUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagProjectUserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('projectUser', EntityType::class, [
                'class' => ProjectUser::class,
                'choice_label' => 'id',
            ])
        ;

        // when the tag does not exist yet the user can either specify the tag ID directly or a new tag name if none of the other tags apply
        if (null === $builder->getData()) {
            $builder->add('tag', EntityType::class, [
                'required' => false,
                'class' => Tag::class,
                'choice_label' => 'id',
            ]);
            $builder->add('tagName', TextType::class, [
                'required' => false,
                'mapped' => false,
            ]);

            // this allows the user to specify a parent tag for the new tag
            $builder->add('parent', EntityType::class, [
                'required' => false,
                'mapped' => false,
                'class' => Tag::class,
                'choice_label' => 'id',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TagProjectUser::class,
            'csrf_protection' => false,
        ]);
    }
}
