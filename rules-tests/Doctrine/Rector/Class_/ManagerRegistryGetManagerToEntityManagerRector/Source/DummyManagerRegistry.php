<?php

declare(strict_types=1);

namespace Rector\Tests\Doctrine\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Source;

final class DummyManagerRegistry
{
    public function getManager(): DummyObjectManager
    {
        return new DummyObjectManager();
    }
}