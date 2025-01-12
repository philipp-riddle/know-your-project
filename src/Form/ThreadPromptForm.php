<?php

namespace App\Form;

use App\Entity\PageSectionAIPrompt;
use App\Entity\Thread;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Note: This entity does not directly exists, it means "create a thread with an attached prompt as a start"
 */
class ThreadPromptForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?Thread */
        $thread = $builder->getData();

        // if the thread is new we need to add the page field
        if (null === $thread) {
            // we cannot map the needed entity 'pageSectionContext' directly as it has to be created first. we can do this in the ThreadApiController
            $builder->add('pageSectionAIPrompt', EntityType::class, [
                'class' => PageSectionAIPrompt::class,
                'choice_label' => 'id',
                'mapped' => false,
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Thread::class,
            'csrf_protection' => false,
        ]);
    }
}
