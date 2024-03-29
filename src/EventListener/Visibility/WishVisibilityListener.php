<?php

declare(strict_types=1);

namespace App\EventListener\Visibility;

use App\Entity\Album;
use App\Entity\Collection;
use App\Entity\Item;
use App\Entity\Photo;
use App\Entity\Wish;
use App\Entity\Wishlist;
use App\Enum\VisibilityEnum;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;

#[AsDoctrineListener(event: Events::prePersist, priority:-2)]
#[AsDoctrineListener(event: Events::onFlush, priority: -2)]
final class WishVisibilityListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Wish) {
            $parentVisibility = $entity->getWishlist()->getFinalVisibility();

            $entity->setParentVisibility($parentVisibility);
            $entity->updateFinalVisibility();
        }
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Wish) {
                $changeset = $uow->getEntityChangeSet($entity);

                $parentVisibility = $entity->getWishlist()->getFinalVisibility();

                if (\array_key_exists('wishlist', $changeset) || \array_key_exists('visibility', $changeset)) {
                    $entity->updateFinalVisibility();
                }
            }
        }
    }
}
