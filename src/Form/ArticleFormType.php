<?php

namespace App\Form;


use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
            ])
            ->add('subtitle', TextType::class, [
                'label' => 'Sous-titre',
            ])
            ->add('content', TextareaType::class, [
                'label'  => false,
                'attr' => [
                    'placeholder' => 'Ici le contenu de l\'article'

                ],

            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo d\'illustration',
            ])
            ->add('category', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name',
                'label' => 'Choisissez une catégorie'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
