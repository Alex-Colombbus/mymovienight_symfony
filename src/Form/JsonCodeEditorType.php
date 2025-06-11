<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\CodeEditorType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JsonCodeEditorType extends AbstractType
{
    /**
     * Ce type de formulaire étend CodeEditorType pour gérer les données JSON.
     * Il transforme entre une chaîne JSON et un tableau associatif.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($arrayToTransform) { // Données du modèle (tableau) vers données du formulaire (chaîne JSON)
                if ($arrayToTransform === null) {
                    return ''; // Retourner une chaîne vide pour un tableau nul
                }

                return json_encode($arrayToTransform, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            },
            function ($stringToTransform) { // Données du formulaire (chaîne JSON) vers données du modèle (tableau)
                if ($stringToTransform === null ||  trim($stringToTransform) === '') {
                    return null; // Ou [] si l'entité attend un tableau vide pour un JSON vide
                }
                $decoded = json_decode($stringToTransform, true);
                if (json_last_error() !== JSON_ERROR_NONE) {

                    return null; // Pour l'instant, retourne null si le JSON est invalide
                }
                return $decoded;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void {}

    public function getParent(): string
    {
        return CodeEditorType::class; // Définir CodeEditorType comme parent
    }
}
