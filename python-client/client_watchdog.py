import os
import subprocess
import time
from datetime import datetime
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent
LOG_DIR = BASE_DIR / "logs"
LOG_FILE = LOG_DIR / "watchdog.log"
STOP_FILE = BASE_DIR / "disable_watchdog.flag"
CLIENT_FILE = BASE_DIR / "client_app.py"

WATCHDOG_PID_FILE = BASE_DIR / "watchdog.pid"
CLIENT_PID_FILE = BASE_DIR / "client.pid"


def write_log(message):
    LOG_DIR.mkdir(exist_ok=True)

    with open(LOG_FILE, "a", encoding="utf-8") as file:
        file.write(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] {message}\n")


def write_pid(path, pid):
    with open(path, "w", encoding="utf-8") as file:
        file.write(str(pid))


def remove_file(path):
    try:
        if path.exists():
            path.unlink()
    except Exception:
        pass


def main():
    write_pid(WATCHDOG_PID_FILE, os.getpid())
    write_log(f"Watchdog started. PID: {os.getpid()}")

    try:
        while True:
            if STOP_FILE.exists():
                write_log("Stop flag found. Watchdog exiting.")
                break

            if not CLIENT_FILE.exists():
                write_log("client_app.py not found. Waiting...")
                time.sleep(10)
                continue

            try:
                write_log("Starting client_app.py")

                process = subprocess.Popen(
                    ["python", str(CLIENT_FILE)],
                    cwd=str(BASE_DIR)
                )

                write_pid(CLIENT_PID_FILE, process.pid)
                write_log(f"Client started. PID: {process.pid}")

                process.wait()

                write_log(f"Client exited with code {process.returncode}")
                remove_file(CLIENT_PID_FILE)

            except Exception as error:
                write_log(f"Watchdog error: {error}")

            time.sleep(3)

    finally:
        remove_file(CLIENT_PID_FILE)
        remove_file(WATCHDOG_PID_FILE)
        write_log("Watchdog stopped.")


if __name__ == "__main__":
    main()