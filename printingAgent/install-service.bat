@echo off
echo Instalando o Printing Agent como serviço do Windows...

:: Cria um arquivo .bat para iniciar o aplicativo
echo @echo off > "%~dp0\start-printing-agent.bat"
echo cd /d "%~dp0" >> "%~dp0\start-printing-agent.bat"
echo start "" "%~dp0\printing-agent.exe" >> "%~dp0\start-printing-agent.bat"

:: Cria uma tarefa agendada para iniciar com o Windows
schtasks /create /tn "PrintingAgent" /tr "%~dp0\start-printing-agent.bat" /sc onlogon /ru SYSTEM /f

echo Serviço instalado com sucesso!
echo O Printing Agent será iniciado automaticamente quando o Windows iniciar.
pause
