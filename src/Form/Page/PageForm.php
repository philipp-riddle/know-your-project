<?php

namespace App\Form\Page;

use App\Entity\Page\Page;
use App\Entity\Project\Project;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name');

        if (null === $builder->getData()) {
            $builder->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'id',
            ]);
            $builder->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
            'csrf_protection' => false,
        ]);
    }
}
