<?php

namespace App\Tests\Api;

use Api\Tests\AuthenticatedTest;
use App\Entity\Tag;
use Symfony\Component\HttpFoundation\Response;

class TagTest extends AuthenticatedTest
{
    public function testGetTags(): void
    {
        $response = $this->createClientWithCredentials()->request('GET', '/api/tags');
        $data = $response->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertEquals(10, $data['hydra:totalItems']);
        $this->assertCount(10, $data['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Tag::class);
    }

    // Interacting with current User's tags
    public function testGetTag(): void
    {
        $tag = $this->em->getRepository(Tag::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($tag);

        $this->createClientWithCredentials()->request('GET', $iri);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri
        ]);
    }

    public function testPutTag(): void
    {
        $tag = $this->em->getRepository(Tag::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($tag);

        $this->createClientWithCredentials()->request('PUT', $iri, ['json' => [
            'label' => 'updated label with PUT',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'label' => 'updated label with PUT',
        ]);
    }

    public function testPatchTag(): void
    {
        $tag = $this->em->getRepository(Tag::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($tag);

        $this->createClientWithCredentials()->request('PATCH', $iri, [
            'headers' => ['Content-Type: application/merge-patch+json'],
            'json' => [
                'label' => 'updated label with PATCH',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'label' => 'updated label with PATCH',
        ]);
    }

    public function testDeleteTag(): void
    {
        $tag = $this->em->getRepository(Tag::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($tag);
        $this->createClientWithCredentials()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    // Interacting with another User's tags
    public function testCantGetAnotherUserTag(): void
    {
        $tag = $this->em->getRepository(Tag::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($tag);

        $this->createClientWithCredentials()->request('GET', $iri);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantPutAnotherUserTag(): void
    {
        $tag = $this->em->getRepository(Tag::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($tag);

        $this->createClientWithCredentials()->request('PUT', $iri, ['json' => [
            'label' => 'updated label with PUT',
        ]]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantPatchAnotherUserTag(): void
    {
        $tag = $this->em->getRepository(Tag::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($tag);

        $this->createClientWithCredentials()->request('PATCH', $iri, [
            'headers' => ['Content-Type: application/merge-patch+json'],
            'json' => [
                'label' => 'updated label with PATCH',
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantDeleteAnotherUserTag(): void
    {
        $tag = $this->em->getRepository(Tag::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($tag);
        $this->createClientWithCredentials()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}