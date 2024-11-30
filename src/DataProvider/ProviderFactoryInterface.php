<?php
namespace Concept\PathAccess\DataProvider;

use Concept\Config\ConfigurableInterface;
use Concept\PathAccess\DataProviderInterface;

interface ProviderFactoryInterface extends ConfigurableInterface
{
    /**
     * Create a data provider instance from the given provider string.
     *
     * @param string $providerString
     * @return DataProviderInterface
     */
    public function create(string $providerString): DataProviderInterface;

}