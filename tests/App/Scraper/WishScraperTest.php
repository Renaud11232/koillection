<?php

declare(strict_types=1);

namespace App\Tests\App\Scraper;

use App\Enum\ScraperTypeEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\PathFactory;
use App\Tests\Factory\ScraperFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class WishScraperTest extends AppTestCase
{
    use Factories;
    use ResetDatabase;

    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
    }

    public function test_can_see_wish_scraper_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/scrapers/wish-scrapers');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Scrapers', $crawler->filter('h1')->text());
    }

    public function test_can_get_wish_scraper(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $scraper = ScraperFactory::createOne(['type' => ScraperTypeEnum::TYPE_WISH, 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/scrapers/wish-scrapers/' . $scraper->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame($scraper->getName(), $crawler->filter('h1')->text());
    }

    public function test_can_add_wish_scraper(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        // Act
        $this->client->request(Request::METHOD_GET, '/scrapers/wish-scrapers/add');

        $crawler = $this->client->submitForm('Submit', [
            'scraper[name]' => 'Manga News'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Manga News', $crawler->filter('h1')->text());
    }

    public function test_can_edit_wish_scraper(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $scraper = ScraperFactory::createOne(['type' => ScraperTypeEnum::TYPE_WISH, 'owner' => $user]);
        PathFactory::createOne(['scraper' => $scraper, 'owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/scrapers/wish-scrapers/' . $scraper->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'scraper[name]' => 'Manga News',
            'scraper[namePath]' => '//h1/text()',
            'scraper[imagePath]' => '//h1/img/@src',
            'scraper[pricePath]' => '//h1/text()',
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Manga News', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('tbody tr'));
    }

    public function test_can_delete_wish_scraper(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $scraper = ScraperFactory::createOne(['type' => ScraperTypeEnum::TYPE_WISH, 'owner' => $user]);
        PathFactory::createOne(['scraper' => $scraper, 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/scrapers/wish-scrapers/' . $scraper->getId());
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/scrapers/wish-scrapers/' . $scraper->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        ScraperFactory::assert()->count(0);
        PathFactory::assert()->count(0);
    }

    public function test_can_export_wish_scraper(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $scraper = ScraperFactory::createOne(['type' => ScraperTypeEnum::TYPE_WISH, 'owner' => $user]);
        PathFactory::createOne(['scraper' => $scraper, 'owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/scrapers/wish-scrapers/' . $scraper->getId() . '/export');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function test_can_import_wish_scraper(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        // Act
        $this->client->request(Request::METHOD_GET, '/scrapers/wish-scrapers');

        $crawler = $this->client->submitForm('Import', [
            'wish_scraper_importer[file]' => $this->createFile('json'),
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        ScraperFactory::assert()->count(1);
        $this->assertSame('Nyancat', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('tbody tr'));

        $this->assertSame('Name path', $crawler->filter('.list-element')->eq(0)->filter('td')->eq(0)->text());
        $this->assertSame('#//h1/text()]#', $crawler->filter('.list-element')->eq(0)->filter('td')->eq(1)->text());
        $this->assertSame('Image path', $crawler->filter('.list-element')->eq(1)->filter('td')->eq(0)->text());
        $this->assertSame('#//h1/img/@src]#', $crawler->filter('.list-element')->eq(1)->filter('td')->eq(1)->text());
        $this->assertSame('Price path', $crawler->filter('.list-element')->eq(2)->filter('td')->eq(0)->text());
        $this->assertSame('', $crawler->filter('.list-element')->eq(2)->filter('td')->eq(1)->text());
    }
}
