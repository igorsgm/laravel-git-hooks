@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION

REM Get the batch filename without extension
SET TOOL_NAME=%~n0

REM Construct path to the corresponding file in the same folder
SET BIN_TARGET=%~dp0%TOOL_NAME%

REM Call php with all arguments
php "%BIN_TARGET%" %*