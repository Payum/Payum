<?php
namespace Payum\Core\Reply;

class HttpRedirect extends HttpResponse
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @param string   $url
     * @param int      $statusCode
     * @param string[] $headers
     */
    public function __construct($url, $statusCode = 302, array $headers = array())
    {
        $this->url = $url;

        $headers['Location'] = $url;

        parent::__construct($this->prepareContent($url), $statusCode, $headers);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param $url
     *
     * @return string
     */
    protected function prepareContent($url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        return sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to %1$s.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
    }
}
