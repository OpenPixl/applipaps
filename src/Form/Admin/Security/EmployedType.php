<?php

namespace App\Form\Admin\Security;

use App\Entity\Admin\Security\Employed;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('firstName')
            ->add('lastName')
            ->add('maidenName')
            ->add('slug')
            ->add('sector')
            ->add('isVerified')
            ->add('avatarName')
            ->add('avatarSize')
            ->add('home')
            ->add('desk')
            ->add('gsm')
            ->add('fax')
            ->add('otherEmail')
            ->add('facebook')
            ->add('instagram')
            ->add('linkedin')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('employedPrez')
            ->add('isWebpublish')
            ->add('numCollaborator')
            ->add('urlWeb')
            ->add('dateEmployed', null, [
                'widget' => 'single_text',
            ])
            ->add('isSupprAvatar')
            ->add('iban')
            ->add('isGdpr')
            ->add('genre')
            ->add('ciFileName')
            ->add('ciFileext')
            ->add('ciFilesize')
            ->add('isSupprCi')
            ->add('civility')
            ->add('agreeTerms')
            ->add('referent', EntityType::class, [
                'class' => Employed::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employed::class,
        ]);
    }
}
