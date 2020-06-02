<?php
/**
 * Parser permettant de décoder une réponse
 *
 * @project sbm
 * @package SbmEsendex/src/Model
 * @filesource MessagesBatchXmlParser.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

class MessagesBatchXmlParser implements ApiSmsInterface
{

    public function __construct()
    {
        $this->oMessagesBatch =  new MessagesBatch();
    }
    public function parse($xml)
    {
        $headers = simplexml_load_string($xml);
        $result = [];
        if ($headers->getName() == "messagebatches") {
            foreach ($headers->account as $messagebatch) {
                $result[] = $this->parseMessageBatch($messagebatch);
            }
        } elseif ($headers->getName() =="messagebatch") {
            $result[] = $this->parseMessageBatch($headers);
        } else {
            throw new XmlException("Xml is missing <messagebatches /> or <messagebatch /> root element");
        }
        return $result;
    }

    private function parseMessageBatch($arrayMessagebatch, $objectMessagesbatch = null)
    {
        if (is_null($objectMessagesbatch)) {
            $objectMessagesbatch = new MessagesBatch();
        }
        foreach ($arrayMessagebatch as $key => $value) {
            if ($key == 'status') {
                $objectMessagesbatch = $this->parseMessageBatch($value, $objectMessagesbatch);
            } elseif (method_exists($objectMessagesbatch, $key)) {
                $objectMessagesbatch->{$key}($value);
            }
        }
        return $objectMessagesbatch;
    }
}