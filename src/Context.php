<?php
namespace Concept\Context;

use Concept\PathAccess\PathAccess;

class Context  implements ContextInterface
{

    private PathAccess $storage;

    protected function getStorage(): PathAccess
    {
        return $this->storage;
    }

}