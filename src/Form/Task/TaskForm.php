<?php

namespace App\Form\Task;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * @var Task
         */
        $data = $builder->getData();
        $isCreatingTask = $data?->getId() === null;

        $builder
            ->add('name', TextType::class, [
                'mapped' => false,
                'required' => true,
            ])
            ->add('stepType', TextType::class, [
                'required' => true,
            ])
        ;
        
        if (!$isCreatingTask) {
            $builder
                ->add('isArchived')
                ->add('dueDate');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'csrf_protection' => false,
        ]);
    }
}
