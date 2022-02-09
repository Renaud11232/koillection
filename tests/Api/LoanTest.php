<?php

namespace App\Tests\Api;

use Api\Tests\AuthenticatedTest;
use App\Entity\Loan;
use Symfony\Component\HttpFoundation\Response;

class LoanTest extends AuthenticatedTest
{
    public function testGetLoans(): void
    {
        $response = $this->createClientWithCredentials()->request('GET', '/api/loans');
        $data = $response->toArray();

        $this->assertResponseIsSuccessful();
        $this->assertEquals(5, $data['hydra:totalItems']);
        $this->assertCount(5, $data['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Loan::class);
    }

    // Interacting with current User's loans
    public function testGetLoan(): void
    {
        $loan = $this->em->getRepository(Loan::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($loan);

        $this->createClientWithCredentials()->request('GET', $iri);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri
        ]);
    }

    public function testPutLoan(): void
    {
        $loan = $this->em->getRepository(Loan::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($loan);

        $this->createClientWithCredentials()->request('PUT', $iri, ['json' => [
            'lentTo' => 'updated lentTo with PUT',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'lentTo' => 'updated lentTo with PUT',
        ]);
    }

    public function testPatchLoan(): void
    {
        $loan = $this->em->getRepository(Loan::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($loan);

        $this->createClientWithCredentials()->request('PATCH', $iri, [
            'headers' => ['Content-Type: application/merge-patch+json'],
            'json' => [
                'lentTo' => 'updated lentTo with PATCH',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'lentTo' => 'updated lentTo with PATCH',
        ]);
    }

    public function testDeleteLoan(): void
    {
        $loan = $this->em->getRepository(Loan::class)->findBy(['owner' => $this->user], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($loan);
        $this->createClientWithCredentials()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    // Interacting with another User's loans
    public function testCantGetAnotherUserLoan(): void
    {
        $loan = $this->em->getRepository(Loan::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($loan);

        $this->createClientWithCredentials()->request('GET', $iri);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantPutAnotherUserLoan(): void
    {
        $loan = $this->em->getRepository(Loan::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($loan);

        $this->createClientWithCredentials()->request('PUT', $iri, ['json' => [
            'lentTo' => 'updated lentTo with PUT',
        ]]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantPatchAnotherUserLoan(): void
    {
        $loan = $this->em->getRepository(Loan::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($loan);

        $this->createClientWithCredentials()->request('PATCH', $iri, [
            'headers' => ['Content-Type: application/merge-patch+json'],
            'json' => [
                'lentTo' => 'updated lentTo with PATCH',
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantDeleteAnotherUserLoan(): void
    {
        $loan = $this->em->getRepository(Loan::class)->findBy(['owner' => $this->otherUser], [], 1)[0];
        $iri = $this->iriConverter->getIriFromItem($loan);
        $this->createClientWithCredentials()->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}