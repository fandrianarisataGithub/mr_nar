<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('matricule', TextType::class, [
                "label" => "Matricule du client:",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('suspendu', HiddenType::class, [
                "attr" => [
                    "value" => "présent"
                ]
            ])
            ->add('nom', TextType::class, [
                "label" => "Nom du client:",
                "attr" => [
                    "placeholder" => "Rakoto...",
                    "class" => "form-control"
                ]
            ])
            ->add('prenom', TextType::class, [
                "label" => "Prénom du client:",
                "attr" => [
                    "placeholder" => "Jean ...",
                    "class" => "form-control"
                ]
            ])
            ->add('cin', IntegerType::class,[
                "label" => "Numéro CIN:",
                "attr" => [
                    "class"=>"form-control",
                    "placeholder" => "101 xxx xxx xxx",
                ]
            ])
            ->add('image_1', FileType::class, [
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypesMessage' => 'Le fichier doit être inférieur à 1024ko',
                    ])
                ],
            ])
            ->add('image_2', FileType::class, [
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypesMessage' => 'Le fichier doit être inférieur à 1024ko',
                    ])
                ],
            ])
            ->add('adresse', TextType::class, [
                "label" => "Adresse exacte du client:",
                "attr" => [
                    "placeholder" => "Lot ...",
                    "class" => "form-control"
                ]
            ])
            ->add('montant', IntegerType::class, [

                "attr" => [
                    "placeholder" => "xxxx Ariary",
                    "class" => "form-control"
                ]
            ])
            ->add('montant_mensuel', IntegerType::class, [
        
                "attr" => [
                    "placeholder" => "xxxx Ariary",
                    "class" => "form-control"
                ]
            ])
            ->add('nbr_versement', NumberType::class, [
               
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('date_debut', DateType::class, [
                "label" => "Date de debut:",
                'widget' => 'choice',   
            ])
            ->add('date_fin', DateType::class, [
                "label" => "Date de fin:",
                'widget' => 'choice',   
            ])
            ->add('register_client', SubmitType::class, [
                "attr" => [
                    "class" => "btn btn-primary",
                    "id" => "register_client"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
