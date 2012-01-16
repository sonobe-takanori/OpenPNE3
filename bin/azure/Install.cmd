@echo off

ECHO "Starting PHP Installation" >> log.txt

md "%~dp0appdata"
cd "%~dp0appdata"

reg add "hku\.default\software\microsoft\windows\currentversion\explorer\user shell folders" /v "Local AppData" /t REG_EXPAND_SZ /d "%~dp0appdata" /f

"..\WebPICmdLine\WebPICmdLine" /Products:PHP53 /AcceptEula >>log.txt 2>>err.txt
"..\WebPICmdLine\WebPICmdLine" /Products:SQLDriverPHP53IIS /AcceptEula >>log.txt 2>>err.txt

reg add "hku\.default\software\microsoft\windows\currentversion\explorer\user shell folders" /v "Local AppData" /t REG_EXPAND_SZ /d %%USERPROFILE%%\AppData\Local /f

copy "..\php_azure.dll" "%ProgramFiles(x86)%\php\v5.3\ext"
copy "..\php_apc.dll" "%ProgramFiles(x86)%\php\v5.3\ext"

ECHO extension=php_azure.dll >> "%ProgramFiles(x86)%\php\v5.3\php.ini"
ECHO extension=php_apc.dll >> "%ProgramFiles(x86)%\php\v5.3\php.ini"

cd ".."

icacls %RoleRoot%\approot /grant "Everyone":F /T
%WINDIR%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%ProgramFiles(x86)%\PHP\v5.3\php-cgi.exe'].environmentVariables.[name='PATH',value='%PATH%;%RoleRoot%\base\x86']" /commit:apphost
%WINDIR%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /-"[fullPath='%ProgramFiles(x86)%\PHP\v5.3\php-cgi.exe'].environmentVariables.[name='AZURE_ROLE_ROOT']" /commit:apphost
%WINDIR%\system32\inetsrv\appcmd.exe set config -section:system.webServer/fastCgi /+"[fullPath='%ProgramFiles(x86)%\PHP\v5.3\php-cgi.exe'].environmentVariables.[name='AZURE_ROLE_ROOT',value='%RoleRoot%']" /commit:apphost

ECHO "Completed PHP Installation" >> log.txt

ECHO "Starting OpenPNE Installation" >> log.txt

copy "databases.yml" "../../config"
copy "OpenPNE.yml" "../../config"

cd "../../"

"%ProgramFiles(x86)%\php\v5.3\php.exe" symfony opPlugin:sync >>bin/azure/log.txt 2>>bin/azure/err.txt
"%ProgramFiles(x86)%\php\v5.3\php.exe" symfony doctrine:build --all --and-load --no-confirmation >>bin/azure/log.txt 2>>bin/azure/err.txt
"%ProgramFiles(x86)%\php\v5.3\php.exe" symfony cc

ECHO "Completed OpenPNE Installation" >> bin/azure/log.txt