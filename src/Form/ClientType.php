<?php

namespace App\Form;

use App\Entity\Client;
use Doctrine\DBAL\Types\FloatType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('cin', NumberType::class,[
                "label" => "Numéro CIN:",
                "attr" => [
                    "class"=>"form-control",
                    "placeholder" => "101 xxx xxx xxx",
                ]
            ])
            ->add('image_1', FileType::class, [
                "label" => "Face avant CIN",
            ])
            ->add('image_2', FileType::class, [
                "label" => "Face arrière CIN"
            ])
            ->add('adresse', TextType::class, [
                "label" => "Adresse exacte du client:",
                "attr" => [
                    "placeholder" => "Lot ...",
                    "class" => "form-control"
                ]
            ])
            ->add('montant', FloatType::class, [
                "label" => "Montant total:",
                "attr" => [
                    "placeholder" => "xxxx Ariary",
                    "class" => "form-control"
                ]
            ])
            ->add('montant_mensuel', FloatType::class, [
                "label" => "Montant mensuel:",
                "attr" => [
                    "placeholder" => "xxxx Ariary",
                    "class" => "form-control"
                ]
            ])
            ->add('nbr_versement', NumberType::class, [
                "label"=>"Nombre de versement:",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('date_debut', DateTimeType::class, [
                "label" => "Date de debut:",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('date_fin', DateTimeType::class, [
                "label" => "Date de fin:",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('register_client', SubmitType::class, [
                "attr" => [
                    "class" => "btn btn-primary form-control",
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
