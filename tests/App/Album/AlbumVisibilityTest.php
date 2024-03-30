<?php

declare(strict_types=1);

namespace App\Tests\App\Album;

use App\Tests\AppTestCase;
use App\Tests\Factory\AlbumFactory;
use App\Tests\Factory\PhotoFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AlbumVisibilityTest extends AppTestCase
{
    use Factories;
    use ResetDatabase;

    #[TestWith(['public', 'public', 'public'])]
    #[TestWith(['public', 'private', 'private'])]
    #[TestWith(['public', 'internal', 'internal'])]

    #[TestWith(['internal', 'public', 'internal'])]
    #[TestWith(['internal', 'private', 'private'])]
    #[TestWith(['internal', 'internal', 'internal'])]

    #[TestWith(['private', 'public', 'private'])]
    #[TestWith(['private', 'private', 'private'])]
    #[TestWith(['private', 'internal', 'private'])]
    public function test_visibility_add_nested_album(string $album1Visibility, string $album3Visibility, string $album2FinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->object();
        $albumLevel1 = AlbumFactory::createOne(['owner' => $user, 'visibility' => $album1Visibility]);

        // Act
        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user, 'visibility' => $album3Visibility]);

        // Assert
        AlbumFactory::assert()->exists(['id' => $albumLevel2->getId(), 'finalVisibility' => $album2FinalVisibility]);
    }

    #[TestWith(['public', 'public', 'public'])]
    #[TestWith(['public', 'private', 'private'])]
    #[TestWith(['public', 'internal', 'internal'])]

    #[TestWith(['internal', 'public', 'internal'])]
    #[TestWith(['internal', 'private', 'private'])]
    #[TestWith(['internal', 'internal', 'internal'])]

    #[TestWith(['private', 'public', 'private'])]
    #[TestWith(['private', 'private', 'private'])]
    #[TestWith(['private', 'internal', 'private'])]
    public function test_visibility_change_parent_album(string $newAlbumVisibility, string $album2Visibility, string $album1FinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->object();
        $albumLevel1 = AlbumFactory::createOne(['parent' => null, 'owner' => $user]);

        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user, 'visibility' => $album2Visibility]);
        $photo2 = PhotoFactory::createOne(['album' => $albumLevel2, 'owner' => $user]);

        // Act
        $newParentAlbum = AlbumFactory::createOne(['owner' => $user, 'visibility' => $newAlbumVisibility]);
        $albumLevel2->setParent($newParentAlbum->object());
        $albumLevel2->save();

        // Assert
        AlbumFactory::assert()->exists(['id' => $albumLevel2->getId(), 'finalVisibility' => $album1FinalVisibility]);
        PhotoFactory::assert()->exists(['id' => $photo2->getId(), 'finalVisibility' => $album1FinalVisibility]);
    }

    #[TestWith(['public', 'public', 'public', 'public'])]
    #[TestWith(['public', 'private', 'public', 'private'])]
    #[TestWith(['public', 'internal', 'public', 'internal'])]

    #[TestWith(['internal', 'public', 'internal', 'internal'])]
    #[TestWith(['internal', 'private', 'internal', 'private'])]
    #[TestWith(['internal', 'internal', 'internal', 'internal'])]

    #[TestWith(['private', 'public', 'private', 'private'])]
    #[TestWith(['private', 'private', 'private', 'private'])]
    #[TestWith(['private', 'internal', 'private', 'private'])]
    public function test_visibility_change_album_visibility(string $album1Visibility, string $album2Visibility, string $level1Visibility, string $level2Visibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->object();
        $albumLevel1 = AlbumFactory::createOne(['parent' => null, 'owner' => $user]);
        $photo1 = PhotoFactory::createOne(['album' => $albumLevel1, 'owner' => $user]);

        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user, 'visibility' => $album2Visibility]);
        $photo2 = PhotoFactory::createOne(['album' => $albumLevel2, 'owner' => $user]);

        // Act
        $albumLevel1->setVisibility($album1Visibility);
        $albumLevel1->save();

        // Assert
        AlbumFactory::assert()->exists(['id' => $albumLevel1->getId(), 'finalVisibility' => $level1Visibility]);
        PhotoFactory::assert()->exists(['id' => $photo1->getId(), 'finalVisibility' => $level1Visibility]);
        AlbumFactory::assert()->exists(['id' => $albumLevel2->getId(), 'finalVisibility' => $level2Visibility]);
        PhotoFactory::assert()->exists(['id' => $photo2->getId(), 'finalVisibility' => $level2Visibility]);
    }
}
