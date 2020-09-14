<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tab_admin["ROLE_ADMIN"] = "ROLE_ADMIN";
        $tab_editeur["ROLE_EDITOR"] = "ROLE_EDITOR";
        $arr = array(["ROLE_ADMIN"],["ROLE_EDITOR"]);

        //echo json_encode($arr);

        $builder
            ->add('usernmane', TextType::class, [
                "label" => "Nom d'utilisateur:",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                    'Editeur' => 'ROLE_EDITOR',
                    'Admin' => 'ROLE_ADMIN',
                ],
                "attr" => [
                    "class" => "form-control",
                ]
            ])
            ->add('password', TextType::class, [
                "label" => "Mot de passe: ",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('nom', TextType::class, [
                "label" => "Nom: ",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('prenom', TextType::class, [
                "label" => "Prénom: ",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('phone', TextType::class, [
                "label" => "Numéro téléphone: ",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('adresse', TextType::class, [
                "label" => "Adresse exacte: ",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('cin', TextType::class, [
                "label" => "Numéro CIN: ",
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('enregistrer', SubmitType::class, [
                "attr" => [
                    "class" => "form-control btn btn-md btn-primary",
                    
                ]
            ])
        ;
        // Data transformer
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray) ? $rolesArray[0] : null;
                },
                function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
