<?php
namespace Payum\Core;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;

class Payment implements PaymentInterface
{
    /**
     * @var \Payum\Core\Action\ActionInterface[]
     */
    protected $actions = array();

    /**
     * @var mixed[]
     */
    protected $apis = array();

    /**
     * @var \Payum\Core\Extension\ExtensionCollection
     */
    protected $extensions;

    /**
     */
    public function __construct()
    {
        $this->extensions = new ExtensionCollection();
    }

    /**
     * @param mixed $api
     * @param bool  $forcePrepend
     *
     * @return void
     */
    public function addApi($api, $forcePrepend = false)
    {
        $forcePrepend ?
            array_unshift($this->apis, $api) :
            array_push($this->apis, $api)
        ;
    }

    /**
     * @param \Payum\Core\Action\ActionInterface $action
     * @param bool                               $forcePrepend
     *
     * @return void
     */
    public function addAction(ActionInterface $action, $forcePrepend = false)
    {
        $forcePrepend ?
            array_unshift($this->actions, $action) :
            array_push($this->actions, $action)
        ;
    }

    /**
     * @param \Payum\Core\Extension\ExtensionInterface $extension
     * @param bool                                     $forcePrepend
     *
     * @return void
     */
    public function addExtension(ExtensionInterface $extension, $forcePrepend = false)
    {
        $this->extensions->addExtension($extension, $forcePrepend);
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request, $catchReply = false)
    {
        $action = null;
        try {
            $this->extensions->onPreExecute($request);

            if (false == $action = $this->findActionSupported($request)) {
                throw RequestNotSupportedException::create($request);
            }

            $this->extensions->onExecute($request, $action);

            $action->execute($request);

            $this->extensions->onPostExecute($request, $action);
        } catch (ReplyInterface $reply) {
            $reply =
                $this->extensions->onReply($reply, $request, $action) ?:
                $reply
            ;

            if ($catchReply) {
                return $reply;
            }

            throw $reply;
        } catch (\Exception $e) {
            $this->extensions->onException($e, $request, $action ?: null);

            throw $e;
        }

        return;
    }

    /**
     * @param mixed $request
     *
     * @return ActionInterface|false
     */
    protected function findActionSupported($request)
    {
        foreach ($this->actions as $action) {
            if ($action->supports($request)) {
                if ($action instanceof PaymentAwareInterface) {
                    $action->setPayment($this);
                }

                if ($action instanceof ApiAwareInterface) {
                    $apiSet = false;
                    foreach ($this->apis as $api) {
                        try {
                            $action->setApi($api);
                            $apiSet = true;
                            break;
                        } catch (UnsupportedApiException $e) {
                        }
                    }

                    if (false == $apiSet) {
                        throw new LogicException(sprintf(
                            'Cannot find right api supported by %s',
                            get_class($action)
                        ));
                    }
                }

                return $action;
            }
        }

        return false;
    }
}
