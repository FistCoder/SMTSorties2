<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Hangout;
use App\Entity\Location;
use App\Entity\State;
use App\Entity\User;
use App\Form\Models\FiltresModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;



class FilterHangoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionner un campus',
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Nom contient',
            ])
            ->add('start', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Entre',
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'et',
            ])
            ->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'label',
                'placeholder' => 'Tous les états',
                'required' => false,
            ])
            ->add('isOrganizer', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'mapped' => true,
            ])
            ->add('isRegistered', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'mapped' => true,
            ])
            ->add('isNotRegistered', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'mapped' => true,
            ])
            ->add('isPast', CheckboxType::class, [
                'required' => false,
                'label' => 'Sorties passées',
                'mapped' => true,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FiltresModel::class, // Liaison vers l'entité ou le model qui servira aux validation et a la maintenance du code
        ]);
    }
}

