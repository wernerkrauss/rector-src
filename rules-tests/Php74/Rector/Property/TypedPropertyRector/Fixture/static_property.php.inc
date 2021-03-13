<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithStaticProperty
{
    /**
     * @var iterable
     */
    private static $iterable;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class ClassWithStaticProperty
{
    private static iterable $iterable;
}

?>