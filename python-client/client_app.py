import json
import socket
import urllib.parse
import urllib.request
import urllib.error
import uuid
import tkinter as tk
from tkinter import simpledialog, messagebox
from datetime import datetime
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent
CONFIG_FILE = BASE_DIR / "config.json"
IDENTITY_FILE = BASE_DIR / "client_identity.json"


def load_config():
    with open(CONFIG_FILE, "r", encoding="utf-8") as file:
        return json.load(file)


def load_json_file(path):
    try:
        if not path.exists():
            return {}

        with open(path, "r", encoding="utf-8") as file:
            return json.load(file)
    except Exception:
        return {}


def save_json_file(path, data):
    with open(path, "w", encoding="utf-8") as file:
        json.dump(data, file, indent=4)


def get_local_ip():
    try:
        test_socket = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        test_socket.connect(("8.8.8.8", 80))
        ip_address = test_socket.getsockname()[0]
        test_socket.close()
        return ip_address
    except Exception:
        try:
            hostname = socket.gethostname()
            return socket.gethostbyname(hostname)
        except Exception:
            return ""


def get_computer_name():
    try:
        return socket.gethostname()
    except Exception:
        return "UNKNOWN-PC"


def get_mac_address():
    try:
        node = uuid.getnode()
        return "-".join(f"{(node >> shift) & 0xff:02X}" for shift in range(40, -1, -8))
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
        self.identity = load_json_file(IDENTITY_FILE)

        self.server_url = self.config["server_url"].rstrip("/")
        self.client_key = self.config["client_key"]
        self.poll_seconds = int(self.config.get("poll_seconds", 5))
        self.admin_pin = str(self.config.get("admin_pin", "2026"))
        self.logo_path = self.config.get("logo_path", "")
        self.business_name = self.config.get("business_name", "Ntozonke Internet Cafe")

        self.computer_name = self.config.get("computer_name", get_computer_name())
        self.mac_address = self.config.get("mac_address", get_mac_address())

        self.registration_token = self.identity.get("registration_token", "")
        self.pc_id = self.identity.get("pc_id", self.config.get("pc_id", ""))
        self.pc_name = self.identity.get(
            "pc_name",
            self.config.get("pc_name", self.computer_name or "Pending PC")
        )
        self.approval_status = self.identity.get("approval_status", "unknown")

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

            if not logo_file.is_absolute():
                logo_file = BASE_DIR / logo_file

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

    def registration_payload(self):
        payload = {
            "computer_name": self.computer_name,
            "ip_address": get_local_ip(),
            "mac_address": self.mac_address
        }

        if self.registration_token:
            payload["registration_token"] = self.registration_token

        return payload

    def base_payload(self):
        payload = {
            "computer_name": self.computer_name,
            "ip_address": get_local_ip(),
            "mac_address": self.mac_address
        }

        if self.registration_token:
            payload["registration_token"] = self.registration_token
        elif self.pc_id:
            payload["pc_id"] = str(self.pc_id)
        elif self.pc_name:
            payload["pc_name"] = self.pc_name

        return payload

    def register_client(self):
        url = f"{self.server_url}/api/client/register"
        response = post_form(url, self.headers(), self.registration_payload())

        if response.get("success"):
            self.update_identity_from_response(response)

        return response

    def ensure_registered(self):
        if not self.registration_token:
            return self.register_client()

        return {
            "success": True,
            "approved": self.approval_status == "approved",
            "approval_status": self.approval_status,
            "action": "registered" if self.approval_status == "approved" else "pending_approval",
            "should_lock": self.approval_status != "approved",
            "pc": {
                "id": self.pc_id,
                "pc_name": self.pc_name,
                "status": "locked",
                "approval_status": self.approval_status
            }
        }

    def update_identity_from_response(self, data):
        pc = data.get("pc", {}) if isinstance(data, dict) else {}

        token = (
            data.get("registration_token")
            or pc.get("client_token")
            or self.registration_token
        )

        approval_status = (
            data.get("approval_status")
            or pc.get("approval_status")
            or self.approval_status
            or "unknown"
        )

        pc_id = pc.get("id", self.pc_id)
        pc_name = pc.get("pc_name", self.pc_name)

        if token:
            self.registration_token = token

        if pc_id:
            self.pc_id = pc_id

        if pc_name:
            self.pc_name = pc_name

        self.approval_status = approval_status

        self.identity = {
            "registration_token": self.registration_token,
            "pc_id": self.pc_id,
            "pc_name": self.pc_name,
            "approval_status": self.approval_status,
            "computer_name": pc.get("computer_name", self.computer_name),
            "ip_address": pc.get("ip_address", get_local_ip()),
            "mac_address": pc.get("mac_address", self.mac_address),
            "last_updated": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        }

        save_json_file(IDENTITY_FILE, self.identity)

        if hasattr(self, "pc_label"):
            self.pc_label.config(text=self.pc_name)

        if hasattr(self, "session_title"):
            self.session_title.config(text=f"{self.pc_name} ACTIVE SESSION")

    def clear_identity(self):
        self.identity = {}
        self.registration_token = ""
        self.pc_id = ""
        self.pc_name = self.computer_name or "Pending PC"
        self.approval_status = "unknown"

        try:
            if IDENTITY_FILE.exists():
                IDENTITY_FILE.unlink()
        except Exception:
            pass

        if hasattr(self, "pc_label"):
            self.pc_label.config(text=self.pc_name)

    def send_heartbeat(self):
        url = f"{self.server_url}/api/client/heartbeat"
        return post_form(url, self.headers(), self.base_payload())

    def get_status(self):
        url = f"{self.server_url}/api/client/status"
        return post_form(url, self.headers(), self.base_payload())

    def show_lock_screen(self, action, status_data=None):
        if status_data:
            self.update_identity_from_response(status_data)

        pc = status_data.get("pc", {}) if status_data else {}
        pc_status = pc.get("status", action)
        approval_status = (
            status_data.get("approval_status")
            if status_data else self.approval_status
        ) or pc.get("approval_status", self.approval_status)

        self.session_window.withdraw()

        if approval_status == "pending" or action == "pending_approval":
            title = "WAITING FOR APPROVAL"
            message = "This computer is waiting for admin approval from the front desk."
        elif approval_status == "rejected" or action == "rejected":
            title = "REGISTRATION REJECTED"
            message = "This computer has not been approved for use."
        elif pc_status == "maintenance":
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
        token_status = "Saved" if self.registration_token else "Not registered"

        self.info_label.config(
            text=(
                f"PC: {self.pc_name}\n"
                f"Computer: {self.computer_name}\n"
                f"Status: {str(pc_status).upper()}\n"
                f"Approval: {str(approval_status).upper()}\n"
                f"Identity: {token_status}\n"
                f"Last check: {now}"
            )
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
            registration_status = self.ensure_registered()

            if not registration_status.get("success"):
                self.show_lock_screen("lock", registration_status)
                self.info_label.config(
                    text=f"Registration error:\n{registration_status.get('message', 'Unknown error')}"
                )
                self.schedule_next_poll()
                return

            if not registration_status.get("approved", False):
                self.show_lock_screen(
                    registration_status.get("action", "pending_approval"),
                    registration_status
                )
                print(
                    f"[{datetime.now().strftime('%H:%M:%S')}] "
                    f"Registration: {registration_status.get('approval_status', 'pending')}"
                )
                self.schedule_next_poll()
                return

            self.send_heartbeat()
            status = self.get_status()

            if status.get("success"):
                self.update_identity_from_response(status)

            if not status.get("success"):
                self.show_lock_screen("lock", {
                    "pc": {
                        "status": "locked",
                        "approval_status": self.approval_status
                    }
                })
                self.info_label.config(
                    text=f"Server error:\n{status.get('message', 'Unknown error')}"
                )
                self.schedule_next_poll()
                return

            if not status.get("approved", True):
                self.show_lock_screen(status.get("action", "pending_approval"), status)
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

            message = body

            try:
                parsed = json.loads(body)
                message = parsed.get("message", body)
            except Exception:
                pass

            if error.code == 404 and "PC station not found" in message:
                self.clear_identity()
                self.show_lock_screen("lock")
                self.info_label.config(
                    text="PC identity was not found on the server.\nThe client will register again on the next check."
                )
            else:
                self.show_lock_screen("lock")
                self.info_label.config(text=f"HTTP Error {error.code}\n{message}")

            print(f"[{datetime.now().strftime('%H:%M:%S')}] HTTP Error: {error.code} - {message}")

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
        print(f"Computer: {self.computer_name}")
        print(f"MAC: {self.mac_address}")
        print(f"PC ID: {self.pc_id or 'Not assigned yet'}")
        print(f"PC Name: {self.pc_name}")
        print(f"Approval: {self.approval_status}")
        print(f"Identity file: {IDENTITY_FILE}")
        print("Admin exit: Ctrl + Shift + Q")
        print("")

        self.poll_server()
        self.root.mainloop()


if __name__ == "__main__":
    app = NtozonkeCafeClient()
    app.run()