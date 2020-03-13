@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../zendframework/zftool/zf.php
php "%BIN_TARGET%" %*
