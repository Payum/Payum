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
        $content = $this->getContent();

        // Remove unexpected utf8 BOM
        if(substr($content, 0, 3) == pack('CCC', 239, 187, 191)) {
            $content = substr($content, 3);
        }

        $json = json_decode($content);
        if (null === $json) {
            throw new LogicException("Response content is not valid json: \n\n".$content);
        }

        return $json;
    }
}
