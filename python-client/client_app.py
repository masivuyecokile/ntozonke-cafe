import json
import time
import socket
import urllib.parse
import urllib.request
import urllib.error
import tkinter as tk
from tkinter import simpledialog, messagebox
from datetime import datetime
from pathlib import Path


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


class NtozonkeCafeClient:
    def __init__(self):
        self.config = load_config()

        self.server_url = self.config["server_url"].rstrip("/")
        self.client_key = self.config["client_key"]
        self.pc_id = self.config["pc_id"]
        self.pc_name = self.config["pc_name"]
        self.poll_seconds = int(self.config.get("poll_seconds", 5))
        self.admin_pin = str(self.config.get("admin_pin", "1234"))
        self.logo_path = self.config.get("logo_path", "")
        self.business_name = self.config.get("business_name", "Ntozonke Internet Cafe")

        self.last_action = None
        self.current_session_id = None
        self.warned_5_minutes = False
        self.warned_1_minute = False
        self.logo_image = None

        self.root = tk.Tk()
        self.root.title("Ntozonke Cafe Client")
        self.root.configure(bg="#07130d")
        self.root.attributes("-fullscreen", True)
        self.root.attributes("-topmost", True)
        self.root.protocol("WM_DELETE_WINDOW", self.disable_close)

        self.root.bind("<Control-Shift-Q>", self.admin_exit)
        self.root.bind("<Escape>", self.ignore_key)

        self.build_lock_screen()
        self.build_session_window()

        self.root.withdraw()
        self.session_window.withdraw()

    def disable_close(self):
        pass

    def ignore_key(self, event=None):
        return "break"

    def admin_exit(self, event=None):
        pin = simpledialog.askstring(
            "Admin Exit",
            "Enter admin PIN:",
            show="*",
            parent=self.root if self.root.state() != "withdrawn" else self.session_window
        )

        if pin == self.admin_pin:
            self.root.destroy()
        else:
            messagebox.showerror("Denied", "Invalid admin PIN.")

    def build_lock_screen(self):
        self.lock_container = tk.Frame(self.root, bg="#07130d")
        self.lock_container.pack(expand=True, fill="both")

        self.center_box = tk.Frame(self.lock_container, bg="#07130d")
        self.center_box.place(relx=0.5, rely=0.48, anchor="center")

        self.load_logo()

        if self.logo_image:
            self.logo_label = tk.Label(
                self.center_box,
                image=self.logo_image,
                bg="#07130d"
            )
            self.logo_label.pack(pady=(0, 20))

        self.brand_label = tk.Label(
            self.center_box,
            text=self.business_name.upper(),
            bg="#07130d",
            fg="#00a651",
            font=("Arial", 25, "bold")
        )
        self.brand_label.pack(pady=(0, 16))

        self.pc_label = tk.Label(
            self.center_box,
            text=self.pc_name,
            bg="#07130d",
            fg="#ffffff",
            font=("Arial", 58, "bold")
        )
        self.pc_label.pack(pady=(0, 8))

        self.status_label = tk.Label(
            self.center_box,
            text="LOCKED",
            bg="#07130d",
            fg="#ffffff",
            font=("Arial", 36, "bold")
        )
        self.status_label.pack(pady=(0, 18))

        self.message_label = tk.Label(
            self.center_box,
            text="Please pay at the front desk to start a session.",
            bg="#07130d",
            fg="#cfe8d8",
            font=("Arial", 19),
            wraplength=900,
            justify="center"
        )
        self.message_label.pack(pady=(0, 26))

        self.info_box = tk.Frame(self.center_box, bg="#0f2418", padx=32, pady=22)
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
            self.lock_container,
            text="Press Ctrl + Shift + Q for admin exit",
            bg="#07130d",
            fg="#7fae8d",
            font=("Arial", 12)
        )
        self.footer_label.pack(side="bottom", pady=24)

    def load_logo(self):
        try:
            if not self.logo_path:
                return

            logo_file = Path(self.logo_path)

            if not logo_file.exists():
                return

            image = tk.PhotoImage(file=str(logo_file))

            max_width = 180
            max_height = 120

            if image.width() > max_width or image.height() > max_height:
                width_ratio = max(1, image.width() // max_width)
                height_ratio = max(1, image.height() // max_height)
                ratio = max(width_ratio, height_ratio)
                image = image.subsample(ratio, ratio)

            self.logo_image = image

        except Exception as error:
            print(f"Logo could not be loaded: {error}")
            self.logo_image = None

    def build_session_window(self):
        self.session_window = tk.Toplevel(self.root)
        self.session_window.title("Cafe Session")
        self.session_window.geometry("360x180+30+30")
        self.session_window.configure(bg="#0f2418")
        self.session_window.attributes("-topmost", True)
        self.session_window.protocol("WM_DELETE_WINDOW", self.disable_close)
        self.session_window.bind("<Control-Shift-Q>", self.admin_exit)

        self.session_title = tk.Label(
            self.session_window,
            text=f"{self.pc_name} ACTIVE SESSION",
            bg="#0f2418",
            fg="#00a651",
            font=("Arial", 14, "bold")
        )
        self.session_title.pack(pady=(16, 6))

        self.session_remaining = tk.Label(
            self.session_window,
            text="Remaining: calculating...",
            bg="#0f2418",
            fg="#ffffff",
            font=("Arial", 22, "bold")
        )
        self.session_remaining.pack(pady=(4, 6))

        self.session_customer = tk.Label(
            self.session_window,
            text="Customer: Walk-in Customer",
            bg="#0f2418",
            fg="#cfe8d8",
            font=("Arial", 12)
        )
        self.session_customer.pack(pady=(2, 2))

        self.session_amount = tk.Label(
            self.session_window,
            text="Amount: R0.00",
            bg="#0f2418",
            fg="#cfe8d8",
            font=("Arial", 12)
        )
        self.session_amount.pack(pady=(2, 8))

        self.session_note = tk.Label(
            self.session_window,
            text="Do not close this window.",
            bg="#0f2418",
            fg="#7fae8d",
            font=("Arial", 10)
        )
        self.session_note.pack()

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

        self.session_window.withdraw()

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

    def show_session_window(self, active_session):
        self.hide_lock_screen()

        remaining_seconds = int(active_session.get("remaining_seconds", 0))
        remaining_text = format_remaining(remaining_seconds)

        customer_name = active_session.get("customer_name", "Walk-in Customer")
        income = float(active_session.get("internet_income", 0))

        self.session_remaining.config(text=f"Remaining: {remaining_text}")
        self.session_customer.config(text=f"Customer: {customer_name}")
        self.session_amount.config(text=f"Amount: R{income:.2f}")

        if remaining_seconds <= 60:
            self.session_remaining.config(fg="#ff4d4d")
        elif remaining_seconds <= 300:
            self.session_remaining.config(fg="#ffc107")
        else:
            self.session_remaining.config(fg="#ffffff")

        self.session_window.deiconify()
        self.session_window.attributes("-topmost", True)
        self.session_window.lift()

        self.handle_warnings(active_session)

    def handle_warnings(self, active_session):
        session_id = active_session.get("id")
        remaining_seconds = int(active_session.get("remaining_seconds", 0))

        if session_id != self.current_session_id:
            self.current_session_id = session_id
            self.warned_5_minutes = False
            self.warned_1_minute = False

        if remaining_seconds <= 300 and remaining_seconds > 60 and not self.warned_5_minutes:
            self.warned_5_minutes = True
            self.show_warning_popup(
                "5 Minutes Remaining",
                "Your internet session will end in less than 5 minutes."
            )

        if remaining_seconds <= 60 and remaining_seconds > 0 and not self.warned_1_minute:
            self.warned_1_minute = True
            self.show_warning_popup(
                "1 Minute Remaining",
                "Your internet session will end in less than 1 minute."
            )

    def show_warning_popup(self, title, message):
        warning = tk.Toplevel(self.root)
        warning.title(title)
        warning.geometry("520x240+420+220")
        warning.configure(bg="#07130d")
        warning.attributes("-topmost", True)

        tk.Label(
            warning,
            text=title,
            bg="#07130d",
            fg="#ffc107",
            font=("Arial", 24, "bold")
        ).pack(pady=(28, 12))

        tk.Label(
            warning,
            text=message,
            bg="#07130d",
            fg="#ffffff",
            font=("Arial", 15),
            wraplength=440,
            justify="center"
        ).pack(pady=(0, 24))

        tk.Button(
            warning,
            text="OK",
            font=("Arial", 12, "bold"),
            bg="#00a651",
            fg="#ffffff",
            padx=22,
            pady=8,
            command=warning.destroy
        ).pack()

        warning.after(15000, warning.destroy)

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

            if should_lock or not active_session:
                self.show_lock_screen(action, status)
                print(f"[{datetime.now().strftime('%H:%M:%S')}] Action: {action} | Lock: True")
            else:
                self.show_session_window(active_session)

                remaining = format_remaining(active_session.get("remaining_seconds", 0))
                income = float(active_session.get("internet_income", 0))

                print(
                    f"[{datetime.now().strftime('%H:%M:%S')}] "
                    f"Action: {action} | Lock: False | "
                    f"Remaining: {remaining} | Amount: R{income:.2f}"
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
        print("Ntozonke Cafe Client App")
        print("------------------------")
        print(f"Server: {self.server_url}")
        print(f"PC ID: {self.pc_id}")
        print(f"PC Name: {self.pc_name}")
        print("Admin exit: Ctrl + Shift + Q")
        print("")

        self.poll_server()
        self.root.mainloop()


if __name__ == "__main__":
    app = NtozonkeCafeClient()
    app.run()