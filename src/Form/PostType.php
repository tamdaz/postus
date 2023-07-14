<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\{SubmitType, TextType};

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $placeholder_arr = [
            "What's up today ?",
            "What's new ?",
            "Type something...",
            "What do you want to talk about today ?"
        ];

        $builder
            ->add('description', TextType::class, [
                'attr' => [
                    'placeholder' => $placeholder_arr[array_rand($placeholder_arr)],
                ],
                'label' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publish'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'translation_domain' => 'posts'
        ]);
    }
}
