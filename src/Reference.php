<?php

namespace Joli\Jane\Runtime;

use League\Uri\Schemes\Generic\AbstractUri;
use League\Uri\Schemes\Http;
use League\Uri\UriParser;
use Rs\Json\Pointer;

/**
 * Deal with a Json Reference
 */
class Reference
{
    /**
     * @var mixed
     */
    private $resolved;

    /**
     * @var AbstractUri
     */
    private $uri;

    /**
     * @var string
     */
    private $original;

    /**
     * @param string $reference
     * @param string $origin
     */
    public function __construct($reference, $origin)
    {
        $uriParse = new UriParser();
        $currentSchemaPath = $uriParse->parse($origin);
        $referencePath = parse_url($reference);
        $mergedPath = array_merge($currentSchemaPath, $referencePath);

        $this->original = $reference;
        $this->uri = Http::createFromComponents($mergedPath);
    }

    /**
     * Resolve a JSON Reference
     *
     * @param callable $deserializeCallback
     *
     * @return mixed Return the json value (deserialized) referenced
     */
    public function resolve(callable $deserializeCallback = null)
    {
        if (null === $deserializeCallback) {
            $deserializeCallback = function ($data) { return $data; };
        }

        if ($this->resolved === null) {
            $this->resolved = $this->doResolve();
        }

        return $deserializeCallback($this->resolved);
    }

    /**
     * Resolve a JSON Reference for a Schema
     *
     * @return mixed Return the json value referenced
     */
    protected function doResolve()
    {
        $json = file_get_contents($this->uri->withFragment(''));
        $pointer = new Pointer($json);

        return $pointer->get($this->uri->getFragment());
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->original;
    }
}
