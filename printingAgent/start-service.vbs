Set WshShell = CreateObject("WScript.Shell")
WshShell.CurrentDirectory = "C:\\Agentimpressao"
WshShell.Run "C:\\Agentimpressao\\printing-agent.exe", 0, False
Set WshShell = Nothing