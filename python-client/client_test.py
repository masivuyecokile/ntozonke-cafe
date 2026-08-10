import json
import time
import socket
import urllib.parse
import urllib.request
import urllib.error
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


def send_heartbeat(config):
    url = f"{config['server_url'].rstrip('/')}/api/client/heartbeat"

    headers = {
        "X-Client-Key": config["client_key"],
        "Content-Type": "application/x-www-form-urlencoded"
    }

    data = {
        "pc_id": str(config["pc_id"]),
        "pc_name": config["pc_name"],
        "ip_address": get_local_ip()
    }

    return post_form(url, headers, data)


def get_client_status(config):
    url = f"{config['server_url'].rstrip('/')}/api/client/status"

    headers = {
        "X-Client-Key": config["client_key"],
        "Content-Type": "application/x-www-form-urlencoded"
    }

    data = {
        "pc_id": str(config["pc_id"]),
        "pc_name": config["pc_name"],
        "ip_address": get_local_ip()
    }

    return post_form(url, headers, data)


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


def main():
    config = load_config()

    print("Ntozonke Cafe Client Test")
    print("-------------------------")
    print(f"Server: {config['server_url']}")
    print(f"PC ID: {config['pc_id']}")
    print(f"PC Name: {config['pc_name']}")
    print("Press Ctrl + C to stop.")
    print("")

    last_action = None

    while True:
        try:
            heartbeat = send_heartbeat(config)

            if not heartbeat.get("success"):
                print(f"[{datetime.now().strftime('%H:%M:%S')}] Heartbeat failed: {heartbeat.get('message')}")
                time.sleep(config["poll_seconds"])
                continue

            status = get_client_status(config)

            if not status.get("success"):
                print(f"[{datetime.now().strftime('%H:%M:%S')}] Status failed: {status.get('message')}")
                time.sleep(config["poll_seconds"])
                continue

            action = status.get("action", "lock")
            should_lock = status.get("should_lock", True)
            active_session = status.get("active_session")

            if action != last_action:
                print("")
                print(f"[{datetime.now().strftime('%H:%M:%S')}] ACTION CHANGED: {action.upper()}")
                last_action = action

            if active_session:
                remaining = format_remaining(active_session.get("remaining_seconds", 0))
                income = active_session.get("internet_income", 0)

                print(
                    f"[{datetime.now().strftime('%H:%M:%S')}] "
                    f"Action: {action} | Lock: {should_lock} | "
                    f"Remaining: {remaining} | Amount: R{income}"
                )
            else:
                print(
                    f"[{datetime.now().strftime('%H:%M:%S')}] "
                    f"Action: {action} | Lock: {should_lock} | No active session"
                )

        except urllib.error.HTTPError as error:
            try:
                body = error.read().decode("utf-8-sig")
            except Exception:
                body = str(error)

            print(f"[{datetime.now().strftime('%H:%M:%S')}] HTTP Error: {error.code} - {body}")

        except Exception as error:
            print(f"[{datetime.now().strftime('%H:%M:%S')}] Client error: {error}")

        time.sleep(config["poll_seconds"])


if __name__ == "__main__":
    main()