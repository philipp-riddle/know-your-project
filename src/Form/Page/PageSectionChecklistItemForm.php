<?php

namespace App\Form\Page;

use App\Entity\PageSectionChecklist;
use App\Entity\PageSectionChecklistItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSectionChecklistItemForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('complete')
        ;

        $pageSectionChecklistItem = $builder->getData();

        if (null === $pageSectionChecklistItem) {
            $builder->add('pageSectionChecklist', EntityType::class, [
                'class' => PageSectionChecklist::class,
                'choice_label' => 'id',
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => PageSectionChecklistItem::class,
        ]);
    }
}
