<?php

namespace App\Tests\Integration\Homepage;

use App\Controller\HomepageController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(HomepageController::class)]
final class HomepageTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('a#login', 'Login');
    }
}
