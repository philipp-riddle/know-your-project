<?php

namespace App\Form\Page;

use App\Entity\Page;
use App\Entity\PageSectionEmbeddedPage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSectionEmbeddedPageForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('page', EntityType::class, [
            'class' => Page::class,
            'choice_label' => 'id',
            'required' => false,
            'empty_data' => null,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => PageSectionEmbeddedPage::class,
        ]);
    }
}
