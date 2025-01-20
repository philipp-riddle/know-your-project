<?php

namespace App\Form\Thread;

use App\Entity\Thread;
use App\Entity\ThreadItemPrompt;
use App\Form\PromptForm;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Note: This entity does not directly exists, it means "create a thread with an attached prompt as a start"
 */
class ThreadItemPromptForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?ThreadItemPrompt */
        $threadItemPrompt = $builder->getData();

        if (null === $threadItemPrompt) {
            // we cannot map the ThreadItem directly here as it does not exist yet; it will be created with the new prompt
            $builder->add('thread', EntityType::class, [
                'class' => Thread::class,
                'mapped' => false,
                'required' => true,
            ]);

            $builder->add('prompt', PromptForm::class, [
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ThreadItemPrompt::class,
            'csrf_protection' => false,
        ]);
    }
}
