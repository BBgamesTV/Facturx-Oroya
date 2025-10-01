<?php

namespace App\Form;

use App\Entity\Facture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroFacture', TextType::class, [
                'label' => 'Numéro de facture'
            ])
            ->add('dateFacture', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date facture'
            ])
            ->add('nomFournisseur', TextType::class)
            ->add('nomAcheteur', TextType::class)
            ->add('totalHT', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Total HT'
            ])
            ->add('totalTTC', MoneyType::class, [
                'currency' => 'EUR',
                'label' => 'Total TTC'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Facture::class,
        ]);
    }
}
