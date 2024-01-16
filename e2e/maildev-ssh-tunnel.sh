#!/bin/bash

# Define the script directory and pidfile
scriptdir="$(dirname "$0")"
pidfile="$scriptdir/ssh-pid.txt"

# Function to start SSH session
start_ssh() {
    ssh -f -N -R 1025:localhost:1025 tng@claudette.mayfirst.org
    # Sleep for a short time to ensure SSH session starts
    sleep 1
    # Use pgrep to find the PID of the SSH process
    pid=$(pgrep -f "ssh -f -N -R 1025:localhost:1025 tng@claudette.mayfirst.org")
    echo $pid > "$pidfile"
    echo "SSH session started. PID: $pid"
}


# Function to stop SSH session
stop_ssh() {
    if [ -f "$pidfile" ]; then
        pid=$(cat "$pidfile")
        kill $pid
        rm "$pidfile"
        echo "SSH session with PID $pid has been stopped."
    else
        echo "PID file not found. Trying to find SSH session using lsof..."
        pid=$(lsof -n -t -i:1025 -sTCP:LISTEN)
        if [ ! -z "$pid" ]; then
            kill $pid
            echo "SSH session with PID $pid has been stopped."
        else
            echo "No SSH session found on port 1025."
        fi
    fi
}

# Function to check the status of the SSH session
status_ssh() {
    if [ -f "$pidfile" ]; then
        if ps -p $(cat "$pidfile") > /dev/null; then
            echo "SSH session is running. PID: $(cat "$pidfile")."
        else
            echo "SSH session not active, but PID file exists."
        fi
    else
        pid=$(lsof -n -t -i:1025 -sTCP:LISTEN)
        if [ ! -z "$pid" ]; then
            echo "SSH session is running. PID: $pid."
        else
            echo "No SSH session is currently running on port 1025."
        fi
    fi
}

# Check command line argument
case "$1" in
    start)
        start_ssh
        ;;
    stop)
        stop_ssh
        ;;
    status)
        status_ssh
        ;;
    *)
        echo "Usage: $0 {start|stop|status}"
        exit 1
        ;;
esac
