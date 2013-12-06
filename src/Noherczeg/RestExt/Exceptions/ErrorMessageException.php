<?php

namespace Noherczeg\RestExt\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Contracts\MessageProviderInterface;
use RuntimeException;

class ErrorMessageException extends \RuntimeException {

    protected $messages;

    public function __construct($messages, $code = 0, Exception $previous = null) {

        if (!($messages instanceof MessageProviderInterface))
        {
            $messages = new MessageBag((array) $messages);
        }

        $this->messages = $messages->getMessageBag();
        $this->messages->setFormat(':message');

        parent::__construct('', $code, $previous);
    }

    public function getMessages() {
        return $this->messages->getMessages();
    }

}