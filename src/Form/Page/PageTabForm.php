<?php

namespace App\Form\Page;

use App\Entity\Page;
use App\Entity\PageTab;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageTabForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?PageTab */
        $pageTab = $builder->getData();

        $builder
            ->add('name')
            ->add('emojiIcon');

        // if the page tab is new we need to add the page field
        if (null === $pageTab) {
            $builder->add('page', EntityType::class, [
                'class' => Page::class,
                'choice_label' => 'id',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PageTab::class,
            'csrf_protection' => false,
        ]);
    }
}
