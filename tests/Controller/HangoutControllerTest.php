<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HangoutControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = self::createClient();
        $client->request('GET', '/hangout');

        self::assertResponseIsSuccessful();
    }

    public function testDetailHangout(): void
    {
        $client = self::createClient();
        $client->getContainer()->get(UserRepository::class)->findBy(['email'=>'user@user.com']);
        $client->request('GET', '/hangout/detail/25');

        if ($client->getResponse()->isSuccessful()) {
            self::assertResponseIsSuccessful();
        }

    }
}
