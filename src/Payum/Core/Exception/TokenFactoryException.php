<?php
namespace Payum\Core\Exception;

class TokenFactoryException extends RuntimeException
{

    /**
     *
     * @param string $path
     * @param array|null $parameters
     * @param \Exception|null $previous
     * @return \Payum\Core\Exception\TokenFactoryException
     */
    public static function couldNotGenerateUrlFor($path, array $parameters = null, \Exception $previous = null)
    {
        // TODO add parameters to exception message
        return new self(sprintf('Could not generate an absolute URL for path "%s".', (string) $path), 1, $previous);
    }
}