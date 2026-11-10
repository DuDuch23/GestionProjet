<?php

namespace App\Controller;

use App\Enum\Priority;
use App\Enum\ProjectStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Living catalogue of the UI components. Not exposed in production.
 */
final class StyleguideController extends AbstractController
{
    #[Route('/styleguide', name: 'styleguide', env: ['dev', 'test'])]
    public function index(): Response
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class, ['label' => 'Nom du projet', 'attr' => ['placeholder' => 'Refonte du site']])
            ->add('email', EmailType::class, ['label' => 'Email du client', 'required' => false])
            ->add('priority', EnumType::class, [
                'label' => 'Priorité',
                'class' => Priority::class,
                'choice_label' => static fn (Priority $p): string => $p->label(),
            ])
            ->add('status', EnumType::class, [
                'label' => 'Statut',
                'class' => ProjectStatus::class,
                'choice_label' => static fn (ProjectStatus $s): string => $s->label(),
                'expanded' => true,
            ])
            ->add('dueDate', DateType::class, ['label' => 'Échéance', 'required' => false])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'help' => '2 000 caractères maximum.'])
            ->add('notify', CheckboxType::class, ['label' => 'Prévenir le client', 'required' => false])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['data-turbo-submits-with' => 'Enregistrement…']])
            ->getForm();

        // Demonstrates the error state of a field.
        $form->get('email')->addError(new FormError('Cette adresse email n\'est pas valide.'));

        return $this->render('styleguide/index.html.twig', [
            'form' => $form,
            'priorities' => Priority::cases(),
            'statuses' => ProjectStatus::cases(),
        ]);
    }
}
