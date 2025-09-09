<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Hangout;
use App\Entity\Location;
use App\Entity\State;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HangoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('startingDateTime', DateType::class, ['widget' => 'single_text', 'format' => 'yyyy-MM-dd'])
            ->add('lastSubmitDate', DateType::class, ['widget' => 'single_text', 'format' => 'yyyy-MM-dd'])
            ->add('length', TimeType::class, ['widget' => 'single_text'])
            ->add('maxParticipant')
            ->add('detail')
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'choice_label' => 'name',
            ])
            ->add('save', SubmitType::class,
                ['label' => 'Enregistrer'])
            ->add('publish', SubmitType::class,
                ['label' => 'Publier'])
            ->add('delete', SubmitType::class,['label' => 'Supprimer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hangout::class,
        ]);
    }
}
