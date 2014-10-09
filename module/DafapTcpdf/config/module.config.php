<?php
return array(
    'tcpdf' => array(),
    'service_manager' => array(
        'invokables' => array(
            'RenderPdfService' => 'DafapTcpdf\Service\RenderPdfService',
            'PdfListener' => 'DafapTcpdf\Listener\PdfListener'
        ),
        'factories' => array(
            //'DafapTcPdf' => 'DafapTcpdf\Service\DafapTcpdf'
        )
    )
);