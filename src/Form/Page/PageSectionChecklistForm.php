<?php

namespace App\Form\Page;

use App\Entity\PageSectionChecklist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSectionChecklistForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?PageSectionChecklist $checklist */
        $checklist = $builder->getData();
        $isUpdate = null !== $checklist?->getId();

        $builder->add('name'); // if it is an update only the name can be updated.

        if (!$isUpdate) {
            $builder
                ->add('pageSectionChecklistItems', CollectionType::class, [
                    'entry_type' => PageSectionChecklistItemForm::class,

                    // 'empty_data' => $checklist?->getPageSectionChecklistItems()->toArray() ?? [],
                    'allow_add' => true, // !$isUpdate, // @todo this triggers one more child item (why is that?) => investigate.
                    // 'by_reference' => false,
                ]);
                // ->add('pageSection', EntityType::class, [
                //     'class' => PageSection::class,
                //     'choice_label' => 'id',
                // ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => PageSectionChecklist::class,
        ]);
    }
}
