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
            ->add('nom', TextType::class, [
                "label" => "Nom du client:",
                "attr" => [
                    "placeholder" => "Rakoto...",
                    "class" => "form-control"
                ]
            ])
            ->add('chapitre', TextType::class, [
                "label" => "Chapitre du client:",
                "attr" => [
                    "placeholder" => "ex : 0811...",
                    "class" => "form-control"
                ]
            ])
            ->add('budget', TextType::class, [
                "label" => "Budget du client:",
                "attr" => [
                    "placeholder" => "ex : 000...",
                    "class" => "form-control"
                ]
            ])
            ->add('article', TextType::class, [
                "label" => "Article du client:",
                "attr" => [
                    "placeholder" => "ex : 110...",
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
            ->add('image_1', FileType::class, array('data_class' => null) ,[ // tsy maintsy misy an'io array io manjary tsy tafa le modif
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypesMessage' => 'Le fichier doit être inférieur à 1024ko',
                    ])
                ],
            ])
            ->add('image_2', FileType::class, array('data_class' => null), [
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
            ->add('nbr_versement', IntegerType::class, [
               
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('date_debut', DateType::class, [
                "label" => "Date de debut:",
                'widget' => 'choice',
                'placeholder' => [
                    'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
                ],
                'format' => 'dd-MM-yyyy', 
            ])
            ->add('createdAt', DateType::class, [
                "label" => "Date du jour: ",
                'widget' => 'choice',
                'placeholder' => [
                    'year' => 'Année', 'month' => 'Mois', 'day' => 'Jour',
                ],
                 'format' => 'dd-MM-yyyy',
            ])
            ->add('numero_bl', TextType::class, [
                "attr" => [
                    "class" => "form-control"
                ],
                
            ])
            ->add('phone', Texttype::class, [
                "attr" => [
                    "class" => "form-control"
                ],

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
