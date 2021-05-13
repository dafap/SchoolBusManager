# Documentation des classes SbmPdf\Model\Table\\...

## Version

Version 2.6.1 du 2 janvier 2021

## Préalables

On doit disposer de la classe `SbmPdf\Model\Attribute\BackgroundAttribute` et du trait `\SbmPdf\Model\Traits\TcpdfTrait`.

## Utilisation

### Création d'une table

On dispose d'une instance de `SbmPdf\Model\Tcpdf`. Il est préférable d'utiliser pour cela le `pdf_manager`. On met en place les caractéristiques du document par les procédures de Tcpdf :

    $pdf = $this->pdf_manager->get(Tcpdf::class);
    $pdf->SetTitle('Mon document PDF');
    $pdf->SetMargins(20, 20, 20);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);
    $pdf->SetAutoPageBreak(true, 9);
    $pdf->SetFont('dejavusans', '', 10);

Ensuite, créer le tableau. Exemple simpe :

    $pdf->AddPage();
    $table = new Table($pdf);
    $table
    ->newRow()
    ->newCell('Nom')
    ->setFontWeight(Table::FONT_WEIGHT_BOLD)
    ->newCell('Prénom')
    ->setFontWeight(Table::FONT_WEIGHT_BOLD)
    ->newCell('eMail')
    ->setFontWeight(Table::FONT_WEIGHT_BOLD)
    ->endRow()
    ->newRow()
    ->newCell('Pomirol')
    ->newCell('Alain')
    ->newCell('dafap@free.fr');
    $table(); // dessine le tableau dans la page PDF courante
    
On peut configurer certaines cellules à la volée. Exemple : 

    $table
    ->newRow()
    ->newCell('Nom')  
    ->setText('Nom de naissance') // surcharge de la propriété text
    ->setFontWeight(Table::FONT_WEIGHT_BOLD)
    ->setAlign('L')
    ->setVerticalAlign('top')
    ->setBorder(1)             // (voir TCPDF::MultiCell)
    ->setRowspan(1)            // rowspan comme en HTML
    ->setColspan(2)            // colspan comme en HTML
    ->setFontSize(10)          // même unité que dans TCPDF
    ->setMinHeight(10)         // comme en CSS
    ->setPadding(2, 4)         // comme en CSS
    ->setPadding(2, 4, 5, 6)   // TRBL (voir la documentation dans Cell)
    ->setWidth(125)            // même unité que dans TCPDF

Utilisation d'un background. Exemple :

    $table
    ->newRow()
    ->newCell('Nom')
    ->setBackgroundColor('#ff4400')      // code couleur RGB
    ->newCell('Prénom')
    ->setBackgroundColor([250, 80, 10])  // code couleur RGB decimal 
    ->newRow()
    ->setBackgroundColor('#dddddd')      // ici, code couleur par défaut pour
    ->newCell('Pomirol')                 // toutes les cellules de la ligne
    ->newCell('Alain')
    ->newCell('dafap@free.fr')
    ->newRow()
    ->setBackgroundColor('transparent')
    ->newCell('Dupont')
    ->newCell('Olive')
    ->newCell(olive.dupont@example.com')
    
Utilisation dans une boucle. Exemple :

    $table
    ->newRow()
    ->setFontWeight(Table::FONT_WEIGHT_BOLD)
    ->newCell('Nom')
    ->newCell('Prénom')
    ->newCell('eMail')
    $alternance = true;
    foreach($data as $individu) {
        $alternance = ! $alternance;
        $table
        ->newRow()
        ->setFontWeight(Table::FONT_WEIGHT_NORMAL)
        ->setBackgroundColor($alternance ? '#dddddd': 'transparent')
        ->newCell($individu->nom)
        ->newCell($individu->prenom)
        ->newCell($individu->email);
    }
    $table();
    
Il est même possible de définir une image d'arrière plan pour chaque cellule. Exemple :

    $table
    ->newRow()
    ->newCell('Nom')
    ->setBackgroundDpi(300)
    ->setBackgroundImage('path/to/my/image.png')
    ->newCell('Prénom)
    ->setBackgroundDpi(150)
    ->setBackgroundImage($binaryImageString); // contenu d'un fichier image
    
Avec une fonction de rappel au changement de page. Exemple :

    $pdf->AddPage();
    $table = new \Tcpdf\Extension\Table\Table($pdf);
    $drawHeaderCallback = function(Table $table) {
        $table
        ->newRow()
        ->newCell('HEADER 1')
        ->setAlign('C')
        ->setFontWeight('bold')
        ->newCell('HEADER 1')
        ->setAlign('C')
        ->setFontWeight('bold');
    };
    // dessiner les cellules d'en-tête la première fois
    $drawHeaderCallback($table);
    // définir la fonction comme rappel
    $table->setPageBreakCallback($drawHeaderCallback);