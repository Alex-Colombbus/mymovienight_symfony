<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\CodeEditorType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JsonCodeEditorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($arrayToTransform) { // Model data (array) to form data (JSON string)
                if ($arrayToTransform === null) {
                    return ''; // Return empty string for null array
                }

                return json_encode($arrayToTransform, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            },
            function ($stringToTransform) { // Form data (JSON string) to model data (array)
                if ($stringToTransform === null ||  trim($stringToTransform) === '') {
                    return null; // Or [] if your entity expects an empty array for empty JSON
                }
                $decoded = json_decode($stringToTransform, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Optionally, throw TransformationFailedException
                    // use Symfony\Component\Form\Exception\TransformationFailedException;
                    // throw new TransformationFailedException('Invalid JSON format.');
                    return null; // For now, returning null if JSON is invalid
                }
                return $decoded;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // You can define default options for your custom type here if needed
        // or allow options to be passed through to the parent.
    }

    public function getParent(): string
    {
        return CodeEditorType::class; // Set CodeEditorType as the parent
    }
}
