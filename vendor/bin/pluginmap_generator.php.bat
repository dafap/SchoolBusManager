@ECHO OFF
SET BIN_TARGET=%~dp0/../zendframework/zendframework/bin/pluginmap_generator.php
php "%BIN_TARGET%" %*
