<?php
/**
 * Tableau de classe Esendex\Model\DispatchArrayMessage permettant d'optimiser l'envoi du
 * même message à une liste de destinataires
 *
 * On factorise pour tous les messages
 *  - 'originator' (auteur) qui sera placé au niveau '<messages>' par le parser
 *  - 'body' (texte du message) qui devra être placé au niveau '<message>' par le parser
 *  - 'type' (SMS ou AUDIO) qui sera placé au niveau '<messages>' par le parser
 *
 * @project sbm
 * @package
 * @filesource DispatchArrayMessage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

use Esendex;

class DispatchArrayMessage implements \ArrayAccess, \Countable, \Iterator, ApiSmsInterface
{

    private $body;

    private $type;

    private $originator;

    private $language;

    private $characterSet;

    private $validityPeriod;

    private $retries;

    private $recipients;

    private $position;

    /**
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @return string
     */
    public function getOriginator()
    {
        return $this->originator;
    }

    /**
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     *
     * @return string
     */
    public function getCharacterSet()
    {
        return $this->characterSet;
    }

    /**
     *
     * @return int
     */
    public function getValidityPeriod()
    {
        return $this->validityPeriod;
    }

    /**
     *
     * @return int
     */
    public function getRetries()
    {
        return $this->retries;
    }

    /**
     *
     * @return multitype:
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     *
     * @return number
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     *
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     *
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     *
     * @param string $originator
     */
    public function setOriginator(string $originator = '')
    {
        $this->originator = $originator;
        return $this;
    }

    /**
     *
     * @param string $language
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     *
     * @param string $characterSet
     */
    public function setCharacterSet($characterSet = null)
    {
        if ($characterSet && $characterSet != Esendex\Model\MessageBody::CharsetGSM &&
            $characterSet != Esendex\Model\MessageBody::CharsetUnicode &&
            $characterSet != Esendex\Model\MessageBody::CharsetAuto) {
            throw new ArgumentException(
                "characterSet() value was '{$characterSet}' and must be one of '" .
                Esendex\Model\MessageBody::CharsetGSM . "', '" .
                Esendex\Model\MessageBody::CharsetUnicode . "' or '" .
                Esendex\Model\MessageBody::CharsetAuto . "'");
        }
        $this->characterSet = $characterSet;
        return $this;
    }

    /**
     *
     * @param int $validityPeriod
     */
    public function setValidityPeriod(int $validityPeriod)
    {
        $this->validityPeriod = $validityPeriod;
        return $this;
    }

    /**
     *
     * @param int $retries
     */
    public function setRetries($retries)
    {
        $this->retries = $retries;
        return $this;
    }

    /**
     *
     * @param string[] $recipients
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
        return $this;
    }

    /**
     *
     * @param int $position
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
        return $this;
    }

    public function reset()
    {
        $this->recipients = [];
        $this->rewind();
        return $this;
    }

    public function __construct(string $body = '',
        string $type = Esendex\Model\Message::SmsType, string $originator = '',
        string $language = 'fr-FR', string $characterSet = null, int $validityPeriod = 0,
        int $retries = null)
    {
        $this->reset()
            ->setBody($body)
            ->setType($type)
            ->setOriginator($originator)
            ->setLanguage($language)
            ->setCharacterSet($characterSet)
            ->setValidityPeriod($validityPeriod)
            ->setRetries($retries);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Iterator::next()
     */
    public function next()
    {
        ++ $this->position;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Iterator::valid()
     */
    public function valid()
    {
        return $this->offsetExists($this->position);
    }

    /**
     *
     * {@inheritdoc}
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->render($offset) : null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Iterator::current()
     */
    public function current()
    {
        return $this->offsetGet($this->position);
    }

    /**
     *
     * {@inheritdoc}
     * @see \ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->recipients[$offset]);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Iterator::rewind()
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        unset($this->recipients[$offset]);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Countable::count()
     */
    public function count()
    {
        return count($this->recipients);
    }

    /**
     *
     * {@inheritdoc}
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        if (! is_null($offset)) {
            throw new ArgumentException(
                "DispatchArrayMessage ne prend pas en charge les décalages définis explicitement.");
        }
        $this->recipients[] = $value;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Iterator::key()
     */
    public function key()
    {
        return $this->position;
    }

    /**
     *
     * @param int $offset
     * @return \Esendex\Model\DispatchMessage
     */
    private function render(int $offset)
    {
        return new Esendex\Model\DispatchMessage($this->originator,
            $this->recipients[$offset], $this->body, $this->type, $this->validityPeriod,
            $this->language, $this->characterSet, $this->retries);
    }
}