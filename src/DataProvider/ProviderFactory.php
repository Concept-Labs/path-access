<?php
namespace Concept\PathAccess\DataProvider;

use Concept\Config\Traits\ConfigurableTrait;
use Concept\Factory\FactoryInterface;

class ProviderFactory implements ProviderFactoryInterface
{
    use ConfigurableTrait;

    private ?FactoryInterface $factory = null;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Create a data provider instance from the given provider string.
     *
     * @param string $providerString
     * @return DataProviderInterface
     */
    public function create(string $providerId): DataProviderInterface
    {

        $provider = $this->getConfig()->get($providerString);

        return $this
            ->getFactory()
            ->create($provider['class'])
            ->withConfig($provider['config']);
    }

    protected function getFactory(): FactoryInterface
    {
        return $this->factory;
    }
}