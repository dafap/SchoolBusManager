# Le Plugin Pdf

## Principe

Le plugin Pdf est enregistré dans le PluginManager (controller_plugin) sous la clé Pdf::PLUGINMANAGER_ID (ici 'documentPdf').
Il est appelé dans les contrôleurs en passant 2 arguments :

 - le Pdf Manager (pdf_manager) qui 
 - un tableau de paramètres (params)
 
### Le Pdf Manager
 
Le PdfManager va permettre de retrouver, bien configurées, les classes du namespace SbmPdf\Model\Document\Template qui étendent la classe SbmPdf\Model\Document\AbstractDocument.

**Attention !**
*La classe Tcpdf du Pdf Manager n'est plus utilisée. Elle pointe sur SbmPdf\Model\Tcpdf (ancienne version).*
 
### Le tableau de paramètres

#### Paramètres obligatoires

#### Paramètres optionnels

