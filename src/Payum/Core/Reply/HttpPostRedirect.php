<?php
namespace Payum\Core\Reply;

class HttpPostRedirect extends HttpResponse
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @param string   $url
     * @param array    $fields
     * @param int      $statusCode
     * @param string[] $headers
     */
    public function __construct($url, array $fields = array(), $statusCode = 200, array $headers = array())
    {
        $this->url = $url;
        $this->fields = $fields;

        parent::__construct($this->prepareContent($url, $fields), $statusCode, $headers);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $url
     * @param array  $fields
     *
     * @return string
     */
    protected function prepareContent($url, array $fields)
    {
        $formInputs = '';
        foreach ($fields as $name => $value) {
            $formInputs .= sprintf(
                '<input type="hidden" name="%1$s" value="%2$s" />',
                htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )."\n";
        }

        $content = <<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <title>Redirecting...</title>
        <style>
            form {
                display: none;
            }
            .container {
                position: absolute;
                margin: auto;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                height: 100px;
                text-align: center;
                font-family: Arial, sans-serif;
                font-size: 13px;
                color: #35414D;
            }

            .loader {
                position: relative;
                display: inline-block;
                width: 40px;
                height: 40px;
                border: 2px solid #0cf;
                border-radius: 50%;
                -webkit-animation: spin 0.75s infinite linear;
                animation: spin 0.75s infinite linear;
            }
            .loader::before,
            .loader::after {
                left: -2px;
                top: -2px;
                display: none;
                position: absolute;
                content: '';
                width: inherit;
                height: inherit;
                border: inherit;
                border-radius: inherit;
            }

            .loader,
            .loader::before {
                display: inline-block;
                border-color: transparent;
                border-top-color: #4D75A4;
            }
            .loader::before {
                -webkit-animation: spin 1.5s infinite ease;
                animation: spin 1.5s infinite ease;
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            @-webkit-keyframes spin {
                from { -webkit-transform: rotate(0deg); }
                to { -webkit-transform: rotate(360deg); }
            }
        </style>
    </head>
    <body onload="document.forms[0].submit();">
        <div class="container">
            <div class="loader"></div>
            <p>Redirecting...</p>
        </div>
        <form action="%1$s" method="post">
            <p>%2$s<input type="submit" value="Continue" /></p>
        </form>
    </body>
</html>
HTML;

        return sprintf($content, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'), $formInputs);
    }
}
