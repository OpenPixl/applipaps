<?php

namespace App\Form\Gestapp\Recos;

use App\Entity\Admin\Security\Employed;
use App\Entity\Gestapp\Recommandations\StatutReco;
use App\Entity\Gestapp\Recommandations\Reco;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\ORM\EntityRepository;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la recommandation'
            ])
            ->add('customerCivility', ChoiceType::class, [
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
            ->add('customerFirstName', TextType::class, [
                'label' => 'Nom & prénoms'
            ])
            ->add('customerLastName')
            ->add('customerMaiden', TextType::class, [
                'label' => 'Nom de jeune fille',
                'required' => false
            ])
            ->add('customerPhone', TextType::class, [
                'label' => 'Contacts'
            ])
            ->add('customerEmail')
            ->add('propertyAddress', TextType::class, [
                'label' => 'Adresse du bien'
            ])
            ->add('propertyComplement')
            ->add('propertyZipcode')
            ->add('propertyCity', TextType::class, [
                'label' => 'commune'
            ])

            ->add('typeProperty', ChoiceType::class,[
                'label' => 'Type de recommandation',
                'choices'  => [
                    'Maison' => 'maison',
                    'Appartement' => 'appartement',
                    'Local commercial' => 'local_commercial',
                    'Terrain' => 'terrain',
                ],
                'choice_attr' => [
                    'Maison' => ['data-data' => 'maison'],
                    'Appartement' => ['data-data' => 'appartement'],
                    'Local commercial' => ['data-data' => 'local_commercial'],
                ],
            ])
            ->add('typeReco', ChoiceType::class,[
                'label' => 'Type de recommandation',
                'choices'  => [
                    'Vendre' => 'vendre',
                    'Louer' => 'louer',
                    'Acheter' => 'Acheter',
                ],
                'choice_attr' => [
                    'Mettre en vente' => ['data-data' => 'Mettre en vente'],
                    'Mettre en location' => ['data-data' => 'Mettre en location'],
                    'Devenir Acquéreur' => ['data-data' => 'Customize Toolbar…']
                ],
            ])
            ->add('isAuthCustomer', CheckboxType::class, [
                'label' => 'En validant la case, je déclare avoir eu le consentement du client prospect fin de communiquer ses coordonnées dans le cadre de son projet immobilier'
            ])
            ->add('commentaires', TextareaType::class, [
                'label' => 'Commentaires',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reco::class,
        ]);
    }
}
