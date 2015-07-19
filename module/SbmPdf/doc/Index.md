# Utilisation du module SbmPdf

## Description du principe général de ce module

Ce module est basé sur la librairie TCPDF qui est installée dans _vendor/technick.com/tcpdf_.
Il comprend 2 classes référencées dans le _service manager_ :
* 'RenderPdfService' => 'SbmPdf\Service\RenderPdfService',
* 'PdfListener' => 'SbmPdf\Listener\PdfListener'

et un ensemble de classes métiers de namespace _SbmPdf\Model_.

La classe _Module_ met en place le listener.

La création d'un document pdf consiste à envoyer un évènement _'renderPdf'_ construit de la façon suivante :
* **nom** : renderPdf
* **target** : service manager
* **arguments** : structure de type array() décrivant le document et son contenu

Pour appeler un document pdf dans une action d'un controller on partiquera par exemple de la façon suivante :

    $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
    $call_pdf->setParam('documentId', 1)
             ->setParam('recordSource', 'Sbm\Db\Table\Classes')
             ->setParam('where', $criteres_obj->getWhere())
             ->setParam('orderBy', 'classeId')
             ->renderPdf();
             
A noter que l'action n'aura peut-être pas de vue associée dans view si le traitement de l'évènement renvoie une réponse http. 
Il faut voir pour cela la configuration du document demandé (dans le formulaire de configuration, champ _Récupération du pdf : en ligne_).

## RenderPdfService

Les méthodes de cette classe permettent de structurer l'argument de l'évènement de la façon suivante :
* **setData($data)** où $data est le nom référencé de la base de données dans le service manager. La clé est _'data'_.
* **setHead($head)** où $head est un tableau des noms de colonnes décrivant l'en-tête. La clé est _'head'_.
* **setPdfConfig($config)** où $config est un tableau de configuration du pdf qui surcharge les valeurs par défaut. La clé est _'pdf_config'_.
* **setTableConfig($config)** où $config est un tableau décrivant la mise en forme des données dans le tableau. La clé est _'table_config'_.
* **setParam($key, $value)** permet d'insérer autant de clés que nécessaire (voir dans l'exemple ci-dessus).

La méthode **renderPdf()** lance l'évènement _'renderPdf'_ avec comme identifiant le nom de la classe : _'SbmPdf\Service\RenderPdfService'_.

## PdfListener

Ce listener écoute les évènements d'identifiant _'SbmPdf\Service\RenderPdfService'_ et de nom _'renderPdf'_. Lorsqu'un tel évènement est reçu, le listener crée une instance de _'SbmPdf\Model\Tcpdf'_ avec comme paramètres le _target_ et l'_arguments_ de l'évènement et lance sa méthode _run()_.

## Présentation d'un document
Voir la page [Présentation d'un document pdf](./Presentation_document1.md).