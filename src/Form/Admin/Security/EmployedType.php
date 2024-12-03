<?php

namespace App\Form\Admin\Security;

use App\Entity\Admin\Security\Employed;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EmployedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            //->add('roles')
            //->add('password')
            ->add('firstName', TextType::class, ['label'=>'Nom & prénom'])
            ->add('lastName')
            ->add('maidenName')
            //->add('slug')
            //->add('sector')
            //->add('isVerified')
            //->add('avatarName')
            //->add('avatarSize')
            ->add('home')
            //->add('desk')
            ->add('gsm', TextType::class, ['label'=>'Contacts'])
            //->add('fax')
            //->add('otherEmail')
            //->add('facebook')
            //->add('instagram')
            //->add('linkedin')
            ->add('isSupprAvatar')
            //->add('iban')
            ->add('isGdpr')
            //->add('genre')
            ->add('ciFile', FileType::class, [
                'label' => "Pièce d'identité",
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10000k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Attention, veuillez charger un fichier au format jpg ou png',
                    ])
                ],
            ])
            //->add('ciFileName')
            //->add('ciFileext')
            //->add('ciFilesize')
            ->add('isSupprCi')
            ->add('civility', ChoiceType::class, [
                'label' => 'Civilité',
                'attr' => [
                    'class' => 'radio-inline'
                ],
                'choices'  => [
                    'M.' => 1,
                    "Mme" => 2,
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('avatarFile', FileType::class,[
                'label' => "avatar",
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10000k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpg',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Attention, veuillez charger un fichier au format jpg ou png',
                    ])
                ],
            ])
            //->add('agreeTerms')
            //->add('referent', EntityType::class, [
            //    'class' => Employed::class,
            //    'choice_label' => 'id',
            //])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employed::class,
        ]);
    }
}
