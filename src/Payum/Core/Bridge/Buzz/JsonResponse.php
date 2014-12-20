<?php
namespace Payum\Core\Bridge\Buzz;

use Buzz\Message\Response;
use Payum\Core\Exception\LogicException;

class JsonResponse extends Response
{
    /**
     * @throws \Payum\Core\Exception\LogicException
     *
     * @return array|object
     */
    public function getContentJson()
    {
        $json = json_decode($this->getContent());
        if (null === $json) {
            throw new LogicException("Response content is not valid json: \n\n{$this->getContent()}");
        }

        return $json;
    }
}
