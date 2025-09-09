<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Hangout;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname')
            ->add('firstname')
            ->add('username')
            ->add('phone')
            ->add('email')

            ->add('confirmPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Password','hash_property_path'=>'password'],
                    'second_options' => ['label' => 'Repeat Password',],
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new Length([
                            'min' => 4,
                            'minMessage' => 'Your password should be at least 4 characters long',
                        ]),
                    ],
                ]
            )
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'id',
            ])
            ->add('userPicture', FileType::class, [
                'label' => 'Ma photo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image(
                        maxSize: '1M',
                        maxSizeMessage: "'L'image ne doit pas dépasser 1 Mo",
                        extensions: ["jpg", "png"],
                        extensionsMessage: "les types autorisés sont .png et .jpg"
                    )
                ]
            ])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
