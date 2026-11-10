<?php

namespace App\Tests\Controller;

final class StyleguideControllerTest extends AuthenticatedWebTestCase
{
    public function testRendersEveryComponentAndTheFormTheme(): void
    {
        $crawler = $this->browser->request('GET', '/styleguide');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'h1');

        // Button variants
        self::assertSelectorExists('button.bg-gray-900');
        self::assertSelectorExists('button[disabled]');
        // Alerts: error uses role="alert"
        self::assertSelectorTextContains('[role="alert"]', 'Le nom du projet est obligatoire.');
        // Enum badges use the French labels
        self::assertSelectorTextContains('body', 'Critique');
        self::assertSelectorTextContains('body', 'En pause');

        // Form theme: required marker, error state, textarea, submit loading label
        self::assertSelectorTextContains('label[for="form_name"]', '*');
        self::assertSelectorExists('input#form_email.border-red-500[aria-invalid="true"]');
        self::assertSelectorTextContains('#form_email_error1', "Cette adresse email n'est pas valide.");
        self::assertSelectorExists('textarea#form_description.resize-none');
        self::assertSelectorExists('button[type="submit"][data-turbo-submits-with="Enregistrement…"]');
        self::assertCount(4, $crawler->filter('input[type="radio"][name="form[status]"]'));
    }
}
