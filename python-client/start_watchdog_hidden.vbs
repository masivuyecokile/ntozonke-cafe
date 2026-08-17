Set fso = CreateObject("Scripting.FileSystemObject")
Set WshShell = CreateObject("WScript.Shell")

clientPath = fso.GetParentFolderName(WScript.ScriptFullName)
logsPath = clientPath & "\logs"

If Not fso.FolderExists(logsPath) Then
    fso.CreateFolder(logsPath)
End If

stopFile = clientPath & "\disable_watchdog.flag"

If fso.FileExists(stopFile) Then
    fso.DeleteFile stopFile
End If

command = "cmd /c cd /d """ & clientPath & """ && python client_watchdog.py"

WshShell.Run command, 0, False