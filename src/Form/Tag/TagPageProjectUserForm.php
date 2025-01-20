<?php

namespace App\Form\Tag;

use App\Entity\ProjectUser;
use App\Entity\TagPage;
use App\Entity\TagPageProjectUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagPageProjectUserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tagPage', EntityType::class, [
                'class' => TagPage::class,
                'choice_label' => 'id',
            ])
            ->add('projectUser', EntityType::class, [
                'class' => ProjectUser::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TagPageProjectUser::class,
            'csrf_protection' => false,
        ]);
    }
}
