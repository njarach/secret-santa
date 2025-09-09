<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('adminEmail',EmailType::class,['label'=>'Adresse email', 'required'=>true])
            ->add('name', TextType::class,['label'=>"Nom de l'événement", 'required'=>true])
            ->add('description', TextType::class,['label'=>"Description (optionnel)"])
            ->add('budget', MoneyType::class,['label'=>"Budget (optionnel)"])
            ->add('submit', SubmitType::class, ['label' => 'Créer!'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
