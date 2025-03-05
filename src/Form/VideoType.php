<?php

namespace App\Form;

use App\Entity\Training;
use App\Entity\Video;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titulo:*',
                'attr' => [
                    'placeholder' => 'Coren-SC apresentação',
                    "class" => "py-3 px-4 block w-full rounded-md text-sm shadow shadow-md dark:bg-dark-input dark:placeholder-placeholder dark:text-dark-text"
                ]
            ])
            ->add('videoId', TextType::class, [
                'label' => 'ID do video:*',
                'attr' => [
                    'placeholder' => '63uQ2sysN4M?si=uzCGfoeS2o6SXoh632132"',
                    "class" => "py-3 px-4 block w-full rounded-md text-sm shadow shadow-md dark:bg-dark-input dark:placeholder-placeholder dark:text-dark-text"
                ]
            ])

            ->add('typeTraining', EntityType::class, [
                'label' => 'TIpo de treinamento:*',
                'class' => Training::class,
                'choice_label' => 'name',
                'placeholder' => '--Selecione--',
                'attr' => [
                    "class" => "py-3 px-4 block w-full rounded-md text-sm shadow shadow-md dark:bg-dark-input dark:placeholder-placeholder dark:text-dark-text"
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
