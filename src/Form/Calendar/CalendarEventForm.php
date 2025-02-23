<?php

namespace App\Form\Calendar;

use App\Entity\Calendar\CalendarEvent;
use App\Entity\Tag\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendarEventForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?CalendarEvent */
        $calendarEvent = $builder->getData();
        $isUpdate = null !== $calendarEvent?->getId();

        $builder->add('name'); // if it is an update only the name can be updated.
        $builder->add('startDate');
        $builder->add('endDate', options: [
            'required' => false, // end date is optional; it can be null.
        ]);

        if (!$isUpdate) {
            $builder
                // project is required for new events.
                ->add('project')
                // user can specify starter tags for the event; this is a list of IDs.
                ->add('tags', CollectionType::class, [
                    'entry_type' => IntegerType::class,
                    'allow_add' => true, // !$isUpdate, // @todo this triggers one more child item (why is that?) => investigate.
                    'required' => false,
                    'mapped' => false, // not directly mapped - in the controller we must create separate TagCalendarEvent entities.
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => CalendarEvent::class,
        ]);
    }
}
