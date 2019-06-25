<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public const KEY_EDIT_ROLES = "edit_roles";
    public const KEY_EDIT_PASSWORD = "edit_password";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = [
            "Editeur" => Member::ROLE_EDITOR,
            "Modérateur" => Member::ROLE_MODERATOR,
            "Manager" => Member::ROLE_MANAGER,
            "Administrateur" => Member::ROLE_ADMIN
        ];

        $builder
            ->add('name')
            ->add('email')
            ->add('picture', ImageType::class)
        ;

        if ($options[self::KEY_EDIT_ROLES]) {
            $builder->add('roles', ChoiceType::class, [
                "choices" => $roles,
                "expanded" => true,
                "multiple" => true
            ]);
        }

        if ($options[self::KEY_EDIT_PASSWORD]) {
            $builder->add('password');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
            self::KEY_EDIT_ROLES => false,
            self::KEY_EDIT_PASSWORD => 0
        ]);
    }
}
