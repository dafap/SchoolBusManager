@echo off
if "%1" == "" goto INFO
php vendor\zendframework\zftool\zf.php classmap generate module/%1 -w
goto FIN
:INFO
echo Usage: maj-classmap nom_module
:FIN