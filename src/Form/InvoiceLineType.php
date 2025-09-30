<?php

namespace App\Form;

use App\Entity\InvoiceLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lineId', NumberType::class, ['label' => 'Line ID'])
            ->add('productName', TextType::class, ['label' => 'Product Name'])
            ->add('sellerId', TextType::class, ['label' => 'Seller ID'])
            ->add('globalId', TextType::class, ['label' => 'Global ID'])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => false
            ])
            ->add('quantity', NumberType::class, ['label' => 'Quantity'])
            ->add('unit', TextType::class, ['label' => 'Unit'])
            ->add('grossPrice', NumberType::class, [
                'label' => 'Gross Price',
                'required' => false
            ])
            ->add('netPrice', NumberType::class, [
                'label' => 'Net Price',
                'required' => false
            ])
            ->add('taxRate', NumberType::class, [
                'label' => 'Tax Rate (%)',
                'required' => false
            ])
            ->add('taxCategory', TextType::class, ['label' => 'Tax Category'])
            ->add('note', TextType::class, [
                'label' => 'Note',
                'required' => false
            ])
            ->add('allowances', CollectionType::class, [
                'entry_type' => AllowanceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Allowances',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InvoiceLine::class,
        ]);
    }
}
