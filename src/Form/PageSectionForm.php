<?php

namespace App\Form;

use App\Entity\PageSection;
use App\Entity\PageTab;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageSectionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        /**
         * @var ?PageSection
         */
        $pageSection = $builder->getData();
        $isNewObject = null === $pageSection;

        if ($isNewObject) {
            $builder->add('pageTab', EntityType::class, [
                'class' => PageTab::class,
                'choice_label' => 'id',
            ]);
        }

        $checklist = $pageSection?->getPageSectionChecklist();
        $text = $pageSection?->getPageSectionText();

        // only add the checklist form field if the page section has a checklist or if it's a new object
        // if its' an existing object we make it required to make sure it's not removed
        // also, make sure to send the data to the form field, i.e. the entity, to add the correct form fields on updating the entity
        if ($isNewObject || null !== $checklist) {
            $builder->add('pageSectionChecklist', PageSectionChecklistForm::class, ['required' => !$isNewObject, 'data' => $checklist]);
        }

        // vice versa for the text form field
        if ($isNewObject || $pageSection?->getPageSectionText()) {
            $builder->add('pageSectionText', PageSectionTextForm::class, ['required' => !$isNewObject, 'data' => $text]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => PageSection::class,
        ]);
    }
}
