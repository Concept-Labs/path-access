<?php
namespace Concept\PathAccess;

interface DataProviderInterface
{
    public function load(string $providerString): array;
}