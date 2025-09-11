<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HangoutControllerTest extends WebTestCase
{
    public function testModifyHangoutIsWorking(): void {
        $client = static::createClient();
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['username' => 'user35']);

        $client->loginUser($user);

        $crawler=$client->request('GET', '/hangouts/modify/35');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Modification de la sortie');
    }

    public function testIndex(): void
    {
        $client = self::createClient();
        $client->request('GET', '/hangouts');

        self::assertResponseIsSuccessful();
    }

    public function testDetailHangout(): void
    {
        $client = self::createClient();

        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email'=>'user@user.com']);

        $client->loginUser($user);

        $client->request('GET', '/hangouts/detail/35');

        $this->assertResponseIsSuccessful();

    }

    public function testAddHangout(): void {
        $client = self::createClient();
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['username' => 'user35']);

        $client->loginUser($user);
        $client->request('GET', '/hangouts/add');




        $client->submitForm('Enregistrer', [
            'hangout[name]'=>'toto',
            'hangout[startingDateTime]'=>'2025-10-05',
            'hangout[lastSubmitDate]'=>'2025-10-03',
            'hangout[length]'=>'01:00',
            'hangout[maxParticipant]'=>'4',
            'hangout[detail]'=>'pas de détail',
            'hangout[location]'=>'84'
        ]);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'toto');

    }
}
