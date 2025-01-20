<?php

namespace App\Form\Page;

use App\Entity\Page\PageSection;
use App\Entity\Page\PageTab;
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

        // only add the checklist form field if the page section has a checklist or if it's a new object
        // if its' an existing object we make it required to make sure it's not removed
        // also, make sure to send the data to the form field, i.e. the entity, to add the correct form fields on updating the entity
        if ($isNewObject || $pageSection?->getPageSectionChecklist()) {
            $builder->add('pageSectionChecklist', PageSectionChecklistForm::class, [
                'required' => !$isNewObject,
                'data' => $pageSection?->getPageSectionChecklist(),
            ]);
        }

        // vice versa for the text form field
        if ($isNewObject || $pageSection?->getPageSectionText()) {
            $builder->add('pageSectionText', PageSectionTextForm::class, [
                'required' => !$isNewObject,
                'data' => $pageSection?->getPageSectionText(),
            ]);
        }

        // vice versa for the url form field
        if ($isNewObject || $pageSection?->getPageSectionURL()) {
            $builder->add('pageSectionURL', PageSectionURLForm::class, [
                'required' => !$isNewObject,
                'data' => $pageSection?->getPageSectionURL(),
            ]);
        }

        if ($isNewObject || $pageSection?->getEmbeddedPage()) {
            $builder->add('embeddedPage', PageSectionEmbeddedPageForm::class, [
                'required' => !$isNewObject,
                'data' => $pageSection?->getEmbeddedPage(),
            ]);
        }

        if ($isNewObject || $pageSection?->getAiPrompt()) {
            $builder->add('aiPrompt', PageSectionAIPromptForm::class, [
                'required' => !$isNewObject,
                'data' => $pageSection?->getAiPrompt(),
            ]);
        }

        if ($isNewObject || $pageSection?->getPageSectionSummary()) {
            $builder->add('pageSectionSummary', PageSectionSummaryForm::class, [
                'required' => !$isNewObject,
                'data' => $pageSection?->getPageSectionSummary(),
            ]);
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
