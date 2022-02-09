<?php

namespace App\Tests\Api;

use Api\Tests\AuthenticatedTest;
use App\Entity\Photo;
use Symfony\Component\HttpFoundation\Response;

class PhotoTest extends AuthenticatedTest
{
    public function testGetPhotos(): void
    {
        $response = $this->createClientWithCredentials()->request('GET', '/api/photos');
        $data = $response->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertEquals(50, $data['hydra:totalItems']);
        $this->assertCount(30, $data['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Photo::class);
    }

    // Interacting with current User's photos
    public function testGetPhoto(): void
    {
        $photo = $this->em->getRepository(Photo::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($photo);

        $this->createClientWithCredentials()->request('GET', $iri);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri
        ]);
    }

    public function testPutPhoto(): void
    {
        $photo = $this->em->getRepository(Photo::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($photo);

        $this->createClientWithCredentials()->request('PUT', $iri, ['json' => [
            'title' => 'updated title with PUT',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'title' => 'updated title with PUT',
        ]);
    }

    public function testPatchPhoto(): void
    {
        $photo = $this->em->getRepository(Photo::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($photo);

        $this->createClientWithCredentials()->request('PATCH', $iri, [
            'headers' => ['Content-Type: application/merge-patch+json'],
            'json' => [
                'title' => 'updated title with PATCH',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'title' => 'updated title with PATCH',
        ]);
    }

    public function testDeletePhoto(): void
    {
        $photo = $this->em->getRepository(Photo::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($photo);
        $this->createClientWithCredentials()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    // Interacting with another User's photos
    public function testCantGetAnotherUserPhoto(): void
    {
        $photo = $this->em->getRepository(Photo::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($photo);

        $this->createClientWithCredentials()->request('GET', $iri);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantPutAnotherUserPhoto(): void
    {
        $photo = $this->em->getRepository(Photo::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($photo);

        $this->createClientWithCredentials()->request('PUT', $iri, ['json' => [
            'title' => 'updated title with PUT',
        ]]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantPatchAnotherUserPhoto(): void
    {
        $photo = $this->em->getRepository(Photo::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($photo);

        $this->createClientWithCredentials()->request('PATCH', $iri, [
            'headers' => ['Content-Type: application/merge-patch+json'],
            'json' => [
                'title' => 'updated title with PATCH',
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantDeleteAnotherUserPhoto(): void
    {
        $photo = $this->em->getRepository(Photo::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($photo);
        $this->createClientWithCredentials()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}