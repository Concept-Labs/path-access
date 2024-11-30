<?php
namespace Concept\PathAccess\DataProvider\Fs;

use Concept\PathAccess\DataProvider\Exception\FileNotFoundException;

class JsonProvider implements JsonProviderInterface
{
    public function load(string $providerString): array
    {

        if (!file_exists($providerString)) {
            throw new FileNotFoundException("File not found: $providerString");
        }
        
        return json_decode(file_get_contents($providerString), true);
    }

}