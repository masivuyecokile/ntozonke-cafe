Set WshShell = CreateObject("WScript.Shell")

clientPath = "C:\xampp\htdocs\ntozonke-cafe\python-client"
command = "cmd /c cd /d """ & clientPath & """ && python client_app.py >> logs\client.log 2>>&1"

WshShell.Run command, 0, False