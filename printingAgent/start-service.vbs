Set WshShell = CreateObject("WScript.Shell")
WshShell.CurrentDirectory = "C:\\Users\\ryanr\\Desktop\\ALDEIA DOS CAMARAS\\venturize-hotelaria\\printingAgent"
WshShell.Run "C:\\Users\\ryanr\\Desktop\\ALDEIA DOS CAMARAS\\venturize-hotelaria\\printingAgent\\printing-agent.exe", 0, False
Set WshShell = Nothing