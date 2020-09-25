<?php

namespace App\Form;

use App\Entity\Pointage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PointageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                "label" => "Nom du pointage : ",
                "attr" => [
                    "placeholder" => "ex : 02-2020",
                    "class" => "form-control",
                ]
            ])
            ->add('nom_lit', TextType::class, [
                "label" => "Nom du pointage (littéral) : ",
                "attr" => [
                    "placeholder" => "ex : Février-2020",
                    "class" => "form-control",
                ]
            ])
            ->add('register', SubmitType::class, [
                
                "attr" => [
                    "value" => "Enregistrer",
                    "class" => "form-control btn btn-primary btn-md",
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pointage::class,
        ]);
    }
}
