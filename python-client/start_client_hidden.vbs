Set fso = CreateObject("Scripting.FileSystemObject")
Set WshShell = CreateObject("WScript.Shell")

clientPath = fso.GetParentFolderName(WScript.ScriptFullName)
logsPath = clientPath & "\logs"

If Not fso.FolderExists(logsPath) Then
    fso.CreateFolder(logsPath)
End If

command = "cmd /c cd /d """ & clientPath & """ && echo Started at %date% %time% >> logs\client.log && python client_app.py >> logs\client.log 2>>&1"

WshShell.Run command, 0, False