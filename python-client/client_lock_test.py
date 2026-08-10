import json
import time
import socket
import urllib.parse
import urllib.request
import urllib.error
import tkinter as tk
from datetime import datetime


def load_config():
    with open("config.json", "r", encoding="utf-8") as file:
        return json.load(file)


def get_local_ip():
    try:
        hostname = socket.gethostname()
        return socket.gethostbyname(hostname)
    except Exception:
        return ""


def post_form(url, headers, data):
    encoded_data = urllib.parse.urlencode(data).encode("utf-8")

    request = urllib.request.Request(
        url=url,
        data=encoded_data,
        headers=headers,
        method="POST"
    )

    with urllib.request.urlopen(request, timeout=10) as response:
        response_text = response.read().decode("utf-8-sig")
        return json.loads(response_text)


def format_remaining(seconds):
    try:
        seconds = int(seconds)
    except Exception:
        return "unknown"

    if seconds <= 0:
        return "expired"

    hours = seconds // 3600
    minutes = (seconds % 3600) // 60
    secs = seconds % 60

    if hours > 0:
        return f"{hours}h {minutes}m {secs}s"

    if minutes > 0:
        return f"{minutes}m {secs}s"

    return f"{secs}s"


class CafeClientLockApp:
    def __init__(self):
        self.config = load_config()
        self.server_url = self.config["server_url"].rstrip("/")
        self.client_key = self.config["client_key"]
        self.pc_id = self.config["pc_id"]
        self.pc_name = self.config["pc_name"]
        self.poll_seconds = int(self.config.get("poll_seconds", 5))

        self.last_action = None
        self.last_status_text = ""

        self.root = tk.Tk()
        self.root.title("Ntozonke Cafe Client")
        self.root.configure(bg="#07130d")

        self.root.attributes("-fullscreen", True)
        self.root.attributes("-topmost", True)
        self.root.protocol("WM_DELETE_WINDOW", self.disable_close)

        # Test/admin exit shortcut.
        # Later we can remove this or protect it with an admin password.
        self.root.bind("<Control-Shift-Q>", self.quit_app)

        self.build_lock_screen()

        # Start hidden until server says lock.
        self.root.withdraw()

    def build_lock_screen(self):
        self.container = tk.Frame(self.root, bg="#07130d")
        self.container.pack(expand=True, fill="both")

        self.center_box = tk.Frame(self.container, bg="#07130d")
        self.center_box.place(relx=0.5, rely=0.5, anchor="center")

        self.brand_label = tk.Label(
            self.center_box,
            text="NTOZONKE INTERNET CAFE",
            bg="#07130d",
            fg="#00a651",
            font=("Arial", 26, "bold")
        )
        self.brand_label.pack(pady=(0, 16))

        self.pc_label = tk.Label(
            self.center_box,
            text=self.pc_name,
            bg="#07130d",
            fg="#ffffff",
            font=("Arial", 54, "bold")
        )
        self.pc_label.pack(pady=(0, 10))

        self.status_label = tk.Label(
            self.center_box,
            text="LOCKED",
            bg="#07130d",
            fg="#ffffff",
            font=("Arial", 34, "bold")
        )
        self.status_label.pack(pady=(0, 18))

        self.message_label = tk.Label(
            self.center_box,
            text="Please pay at the front desk to start a session.",
            bg="#07130d",
            fg="#cfe8d8",
            font=("Arial", 18),
            wraplength=850,
            justify="center"
        )
        self.message_label.pack(pady=(0, 24))

        self.info_box = tk.Frame(self.center_box, bg="#0f2418", padx=28, pady=20)
        self.info_box.pack(pady=(0, 20))

        self.info_label = tk.Label(
            self.info_box,
            text="Waiting for admin...",
            bg="#0f2418",
            fg="#ffffff",
            font=("Arial", 16),
            justify="center"
        )
        self.info_label.pack()

        self.footer_label = tk.Label(
            self.container,
            text="System managed by Simple Perfect Solutions",
            bg="#07130d",
            fg="#7fae8d",
            font=("Arial", 12)
        )
        self.footer_label.pack(side="bottom", pady=24)

    def disable_close(self):
        pass

    def quit_app(self, event=None):
        self.root.destroy()

    def headers(self):
        return {
            "X-Client-Key": self.client_key,
            "Content-Type": "application/x-www-form-urlencoded"
        }

    def base_payload(self):
        return {
            "pc_id": str(self.pc_id),
            "pc_name": self.pc_name,
            "ip_address": get_local_ip()
        }

    def send_heartbeat(self):
        url = f"{self.server_url}/api/client/heartbeat"
        return post_form(url, self.headers(), self.base_payload())

    def get_status(self):
        url = f"{self.server_url}/api/client/status"
        return post_form(url, self.headers(), self.base_payload())

    def show_lock_screen(self, action, status_data=None):
        pc = status_data.get("pc", {}) if status_data else {}
        pc_status = pc.get("status", action)

        if pc_status == "maintenance":
            title = "MAINTENANCE"
            message = "This computer is currently under maintenance."
        elif pc_status == "offline":
            title = "OFFLINE"
            message = "This computer is currently unavailable."
        else:
            title = "LOCKED"
            message = "Please pay at the front desk to start a session."

        self.status_label.config(text=title)
        self.message_label.config(text=message)

        now = datetime.now().strftime("%H:%M:%S")
        self.info_label.config(
            text=f"PC: {self.pc_name}\nStatus: {pc_status.upper()}\nLast check: {now}"
        )

        self.root.deiconify()
        self.root.attributes("-fullscreen", True)
        self.root.attributes("-topmost", True)
        self.root.lift()
        self.root.focus_force()

    def hide_lock_screen(self):
        self.root.withdraw()

    def poll_server(self):
        try:
            self.send_heartbeat()
            status = self.get_status()

            if not status.get("success"):
                self.show_lock_screen("lock", {
                    "pc": {
                        "status": "locked"
                    }
                })
                self.info_label.config(
                    text=f"Server error:\n{status.get('message', 'Unknown error')}"
                )
                self.schedule_next_poll()
                return

            action = status.get("action", "lock")
            should_lock = status.get("should_lock", True)
            active_session = status.get("active_session")

            if action != self.last_action:
                print(f"[{datetime.now().strftime('%H:%M:%S')}] ACTION CHANGED: {action.upper()}")
                self.last_action = action

            if should_lock:
                self.show_lock_screen(action, status)
                print(f"[{datetime.now().strftime('%H:%M:%S')}] Action: {action} | Lock: True")
            else:
                self.hide_lock_screen()

                if active_session:
                    remaining = format_remaining(active_session.get("remaining_seconds", 0))
                    income = active_session.get("internet_income", 0)

                    print(
                        f"[{datetime.now().strftime('%H:%M:%S')}] "
                        f"Action: {action} | Lock: False | "
                        f"Remaining: {remaining} | Amount: R{income}"
                    )
                else:
                    print(
                        f"[{datetime.now().strftime('%H:%M:%S')}] "
                        f"Action: {action} | Lock: False | No active session"
                    )

        except urllib.error.HTTPError as error:
            try:
                body = error.read().decode("utf-8-sig")
            except Exception:
                body = str(error)

            self.show_lock_screen("lock")
            self.info_label.config(text=f"HTTP Error {error.code}\n{body}")
            print(f"[{datetime.now().strftime('%H:%M:%S')}] HTTP Error: {error.code} - {body}")

        except Exception as error:
            self.show_lock_screen("lock")
            self.info_label.config(text=f"Connection problem\n{error}")
            print(f"[{datetime.now().strftime('%H:%M:%S')}] Client error: {error}")

        self.schedule_next_poll()

    def schedule_next_poll(self):
        self.root.after(self.poll_seconds * 1000, self.poll_server)

    def run(self):
        print("Ntozonke Cafe Lock Client Test")
        print("------------------------------")
        print(f"Server: {self.server_url}")
        print(f"PC ID: {self.pc_id}")
        print(f"PC Name: {self.pc_name}")
        print("Press Ctrl + Shift + Q to exit the lock screen test.")
        print("")

        self.poll_server()
        self.root.mainloop()


if __name__ == "__main__":
    app = CafeClientLockApp()
    app.run()